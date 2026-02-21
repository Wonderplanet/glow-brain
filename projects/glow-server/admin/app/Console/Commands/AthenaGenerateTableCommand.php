<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Constants\AthenaConstant;
use App\Constants\Database;
use App\Constants\DatalakeConstant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AthenaGenerateTableCommand extends Command
{
    /**
     * 出力ディレクトリのフォーマット
     * {env}: 環境名、{database}: データベース名
     */
    private const OUTPUT_DIR_FORMAT = 'database/athena_tables/{env}/{database}';

    /**
     * テンプレートファイルのパス
     * CSV形式、日次パーティション（dt）を使用するAthenaテーブル定義
     */
    private const TEMPLATE_FILE_PATH = 'database/athena_tables/templates/athena_create_table_csv_daily.sql';

    /**
     * 対象テーブルのプレフィックス（全件取得対象）
     * このプレフィックスで始まるテーブルが全件処理対象
     */
    private const TARGET_TABLE_PREFIXES = ['log_'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:athena:generate-table
                            {--table= : 特定のテーブル名を指定（未指定の場合は全log/usrテーブル）}
                            {--target-env=develop : 環境名（prod/staging/dev-ldなど）}
                            {--bucket= : S3バケット名（未指定の場合はglow-{target-env}-datalake）}
                            {--database= : CREATE TABLE文内のデータベース名（必須）}
                            {--start-date=2025/07/01 : パーティション開始日}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'データベーステーブルからAthena用のCREATE TABLE文を生成';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tableName = $this->option('table');
        $env = $this->option('target-env');
        $bucket = $this->option('bucket');
        $database = $this->option('database');
        $startDate = $this->option('start-date');

        // databaseオプションの必須チェック
        if (empty($database)) {
            $this->error('--database オプションは必須です。');
            return Command::FAILURE;
        }

        // bucketのデフォルト値を設定
        if (empty($bucket)) {
            $bucket = "glow-{$env}-datalake";
        }

        $this->info("環境: {$env}");
        $this->info("S3バケット: {$bucket}");
        $this->info("CREATE TABLE文のデータベース名: {$database}");
        $this->newLine();

        // 出力ディレクトリの準備
        $outputDir = $this->getOutputDirectory($env, $database);
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("ディレクトリを作成しました: {$outputDir}");
        }

        // データベース接続
        $connection = Database::TIDB_CONNECTION;

        try {
            if ($tableName) {
                // 特定テーブルのみ生成
                $this->generateTableQuery($connection, $tableName, $bucket, $database, $startDate, $outputDir);
            } else {
                // 対象テーブルを生成
                $tables = $this->getTargetTables($connection);
                $this->info("対象テーブル数: " . count($tables));
                $this->newLine();

                $bar = $this->output->createProgressBar(count($tables));
                $bar->start();

                foreach ($tables as $table) {
                    $this->generateTableQuery($connection, $table, $bucket, $database, $startDate, $outputDir);
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            }

            $this->newLine();
            $this->info("✓ Athenaテーブル定義の生成が完了しました");
            $this->info("出力先: {$outputDir}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("エラーが発生しました: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 出力ディレクトリのパスを取得
     *
     * @param string $env 環境名
     * @param string $database データベース名
     * @return string 出力ディレクトリの絶対パス
     */
    private function getOutputDirectory(string $env, string $database): string
    {
        $path = str_replace(
            ['{env}', '{database}'],
            [$env, $database],
            self::OUTPUT_DIR_FORMAT
        );

        return base_path($path);
    }

    /**
     * 対象テーブル一覧を取得
     */
    private function getTargetTables(string $connection): array
    {
        $tables = [];

        // log_系テーブルなど、プレフィックスベースで全件取得
        foreach (self::TARGET_TABLE_PREFIXES as $prefix) {
            $results = DB::connection($connection)
                ->select("SHOW TABLES LIKE '{$prefix}%'");

            foreach ($results as $result) {
                $tableKey = 'Tables_in_' . DB::connection($connection)->getDatabaseName() . " ({$prefix}%)";
                $tables[] = $result->{$tableKey};
            }
        }

        // usr_系テーブルはDatalakeConstantのホワイトリストのみ追加
        foreach (DatalakeConstant::WP_DATALAKE_S3_UPLOAD_TARGET_TABLES as $tableName) {
            // テーブルの存在確認
            $results = DB::connection($connection)
                ->select("SHOW TABLES LIKE ?", [$tableName]);

            if (!empty($results)) {
                $tables[] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * テーブルのカラム情報を取得してAthena CREATE TABLE文を生成
     */
    private function generateTableQuery(
        string $connection,
        string $tableName,
        string $bucket,
        string $database,
        string $startDate,
        string $outputDir
    ): void {
        // カラム情報を取得
        $columns = DB::connection($connection)
            ->select("
                SELECT
                    COLUMN_NAME,
                    DATA_TYPE,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ", [$tableName]);

        if (empty($columns)) {
            $this->warn("テーブルが見つかりません: {$tableName}");
            return;
        }

        // CREATE TABLE文を生成
        $createTableSql = $this->buildCreateTableStatement(
            $database,
            $tableName,
            $columns,
            $bucket,
            $startDate
        );

        // ファイルに保存
        $outputFile = "{$outputDir}/{$tableName}.sql";
        File::put($outputFile, $createTableSql);
    }

    /**
     * CREATE TABLE文を構築
     */
    private function buildCreateTableStatement(
        string $database,
        string $tableName,
        array $columns,
        string $bucket,
        string $startDate
    ): string {
        $columnDefinitions = [];

        foreach ($columns as $column) {
            $athenaType = $this->convertToAthenaType($column->DATA_TYPE, $column->IS_NULLABLE === 'YES');
            $comment = $this->escapeComment($column->COLUMN_COMMENT);
            $columnDefinitions[] = "    `{$column->COLUMN_NAME}` {$athenaType} COMMENT '{$comment}'";
        }

        $columnsSql = implode(",\n", $columnDefinitions);

        // S3パスを生成
        $s3Location = str_replace(
            ['{bucket}', '{table}'],
            [$bucket, $tableName],
            AthenaConstant::S3_DATA_SOURCE_PATH_FORMAT
        );

        $s3PartitionPath = str_replace(
            ['{bucket}', '{table}'],
            [$bucket, $tableName],
            AthenaConstant::S3_PARTITION_PATH_FORMAT
        );

        // テンプレートファイルを読み込み
        $template = File::get(base_path(self::TEMPLATE_FILE_PATH));

        // プレースホルダーを置換
        $sql = str_replace(
            ['{{tableName}}', '{{database}}', '{{columnDefinitions}}', '{{s3Location}}', '{{s3PartitionPath}}', '{{startDate}}'],
            [$tableName, $database, $columnsSql, $s3Location, $s3PartitionPath, $startDate],
            $template
        );

        return $sql;
    }

    /**
     * MySQLのデータ型をAthenaのデータ型に変換
     *
     * OpenCSVSerdeでは\N（NULL値）の処理に問題があるため、
     * NULLABLEなカラムはstring型として定義し、クエリ側でCAST/型変換を行う
     *
     * @param string $mysqlType MySQLのデータ型
     * @param bool $isNullable NULLを許可するか（IS_NULLABLE = 'YES'）
     * @return string Athenaのデータ型
     */
    private function convertToAthenaType(string $mysqlType, bool $isNullable): string
    {
        // NULLABLEな列は全てstring型にする（\N処理の問題回避）
        if ($isNullable) {
            return 'string';
        }

        // NOT NULLな列は適切な型を使用
        return match ($mysqlType) {
            'tinyint', 'smallint', 'mediumint', 'int', 'integer' => 'int',
            'bigint' => 'bigint',
            'float' => 'float',
            'double', 'decimal' => 'double',
            'date' => 'date',
            'datetime', 'timestamp' => 'string', // CSVではstringとして扱う
            'time' => 'string',
            'year' => 'int',
            'char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext' => 'string',
            'binary', 'varbinary', 'blob', 'tinyblob', 'mediumblob', 'longblob' => 'string',
            'json' => 'string', // JSONもCSVではstring
            'enum', 'set' => 'string',
            default => 'string',
        };
    }

    /**
     * コメント内のシングルクォートをエスケープ
     */
    private function escapeComment(string $comment): string
    {
        return str_replace("'", "''", $comment);
    }
}
