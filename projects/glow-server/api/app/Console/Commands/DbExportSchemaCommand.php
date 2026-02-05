<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Constants\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

/**
 * 全DBテーブルのスキーマ情報を複数フォーマットでエクスポートするコマンド
 *
 * local/testing/local_test 環境でのみ実行可能
 * マイグレーション実行後に自動でスキーマファイルを更新し、
 * git diff で変更を検出できるようにする
 *
 * 出力先: database/schema/exports/
 *
 * 出力ファイル:
 *   - master_tables_ddl.sql: mst, mng のDDL（--format=sqlの場合）
 *   - master_tables_schema.yml: mst, mng のスキーマ（--format=ymlの場合）
 *   - master_tables_schema.json: mst, mng のスキーマ（--format=jsonの場合）
 *   - user_tables_ddl.sql: usr, log, sys のDDL（--format=sqlの場合）
 *   - user_tables_schema.yml: usr, log, sys のスキーマ（--format=ymlの場合）
 *   - user_tables_schema.json: usr, log, sys のスキーマ（--format=jsonの場合）
 *
 * オプション:
 *   (指定なし)        : 全形式を出力（デフォルト: sql,yml,json）
 *   --format=sql      : SQL DDL形式のみ出力
 *   --format=yml      : YAML形式のみ出力
 *   --format=json     : JSON形式のみ出力
 *   --format=sql,yml  : SQLとYAMLの両方を出力
 *   --format=sql,yml,json : 全形式を出力（明示的指定）
 */
class DbExportSchemaCommand extends Command
{
    protected $signature = 'db:export-schema {--format= : 出力形式 (sql|yml|json またはカンマ区切り。デフォルト: 全形式)}';

    protected $description = '全DBテーブルのスキーマ情報を複数フォーマットでエクスポート（local/testing/local_test環境のみ）';

    /**
     * TiDBテーブルのプレフィックス（論理DB分割用）
     */
    private const TIDB_PREFIXES = [
        'usr_' => 'usr',
        'log_' => 'log',
        'sys_' => 'sys',
    ];

    /**
     * エクスポート対象外のテーブル
     */
    private const EXCLUDED_TABLES = [
        'migrations',
    ];

    /**
     * 有効なフォーマット
     */
    private const VALID_FORMATS = ['sql', 'yml', 'json'];

    public function handle(): int
    {
        // 環境チェック: 対象外環境では何も出力せずに終了
        $env = config('app.env');
        if (!in_array($env, ['local_test', 'testing'], true)) {
            return 0;
        }

        dump('DBスキーマエクスポート');

        try {
            // フォーマットのバリデーションとパース
            $formats = $this->validateAndParseFormats();
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $outputDir = database_path('schema/exports');

        // 出力ディレクトリ作成
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info('全DBテーブルのスキーマをエクスポート中...');
        $this->newLine();

        // MySQL系（mst, mng）をエクスポート
        $this->exportMysqlData($outputDir, $formats);

        // TiDB系（usr, log, sys）をエクスポート
        $this->exportTidbData($outputDir, $formats);

        $this->newLine();
        $this->info("エクスポート完了!");

        return 0;
    }

    /**
     * フォーマットオプションをバリデーションしてパース
     */
    private function validateAndParseFormats(): array
    {
        $formatOption = $this->option('format') ?? 'sql,yml,json';
        $formats = array_map('trim', explode(',', $formatOption));
        $formats = array_unique($formats);

        // 無効なフォーマットをチェック
        $invalidFormats = array_diff($formats, self::VALID_FORMATS);
        if (!empty($invalidFormats)) {
            throw new \InvalidArgumentException(
                '無効なフォーマット: ' . implode(', ', $invalidFormats) . "\n" .
                '有効なフォーマット: ' . implode(', ', self::VALID_FORMATS)
            );
        }

        return $formats;
    }

    /**
     * MySQL系データベースのエクスポート
     */
    private function exportMysqlData(string $outputDir, array $formats): void
    {
        // データ収集
        $databases = [
            'mst' => $this->exportMysqlConnection(Database::MST_CONNECTION, 'mst'),
            'mng' => $this->exportMysqlConnection(Database::MNG_CONNECTION, 'mng'),
        ];

        // フォーマットごとに出力
        if (in_array('sql', $formats, true)) {
            $this->writeSqlFile($outputDir, 'master_tables_ddl.sql', $databases, 'Master Tables DDL (mst, mng)');
        }

        if (in_array('yml', $formats, true)) {
            $schemaData = ['databases' => $this->extractSchemaData($databases)];
            $this->writeYmlFile($outputDir, 'master_tables_schema.yml', $schemaData);
        }

        if (in_array('json', $formats, true)) {
            $schemaData = ['databases' => $this->extractSchemaData($databases)];
            $this->writeJsonFile($outputDir, 'master_tables_schema.json', $schemaData);
        }
    }

    /**
     * TiDB系データベースのエクスポート
     */
    private function exportTidbData(string $outputDir, array $formats): void
    {
        // データ収集
        $databases = [];
        foreach (self::TIDB_PREFIXES as $prefix => $alias) {
            $databases[$alias] = $this->exportTidbTables(
                Database::TIDB_CONNECTION,
                $prefix,
                $alias
            );
        }

        // フォーマットごとに出力
        if (in_array('sql', $formats, true)) {
            $this->writeSqlFile($outputDir, 'user_tables_ddl.sql', $databases, 'User Tables DDL (usr, log, sys)');
        }

        if (in_array('yml', $formats, true)) {
            $schemaData = ['databases' => $this->extractSchemaData($databases)];
            $this->writeYmlFile($outputDir, 'user_tables_schema.yml', $schemaData);
        }

        if (in_array('json', $formats, true)) {
            $schemaData = ['databases' => $this->extractSchemaData($databases)];
            $this->writeJsonFile($outputDir, 'user_tables_schema.json', $schemaData);
        }
    }

    /**
     * MySQL接続からデータをエクスポート
     */
    private function exportMysqlConnection(string $connection, string $alias): array
    {
        $this->info("  処理中: {$alias} ({$connection}) ...");

        try {
            $tables = DB::connection($connection)
                ->select('SHOW TABLES');

            if (empty($tables)) {
                $this->warn("    警告: テーブルが見つかりません");
                return [
                    'connection' => $connection,
                    'alias' => $alias,
                    'tables' => [],
                ];
            }

            $tablesData = [];
            $tableCount = 0;

            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];

                // 除外テーブルをスキップ
                if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    continue;
                }

                $tablesData[$tableName] = [
                    'schema' => $this->getTableSchema($connection, $tableName),
                    'ddl' => $this->getCreateTableStatement($connection, $tableName),
                ];

                if ($tablesData[$tableName]['schema'] || $tablesData[$tableName]['ddl']) {
                    $tableCount++;
                }
            }

            $this->info("    完了: {$tableCount} テーブル");

            return [
                'connection' => $connection,
                'alias' => $alias,
                'tables' => $tablesData,
            ];
        } catch (\Exception $e) {
            $this->error("    エラー: " . $e->getMessage());
            return [
                'connection' => $connection,
                'alias' => $alias,
                'tables' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * TiDBテーブルをエクスポート
     */
    private function exportTidbTables(string $connection, string $prefix, string $alias): array
    {
        $this->info("  処理中: {$alias} (TiDB: local, prefix: {$prefix}) ...");

        try {
            $tables = DB::connection($connection)
                ->select("SHOW TABLES LIKE ?", ["{$prefix}%"]);

            if (empty($tables)) {
                $this->warn("    警告: テーブルが見つかりません");
                return [
                    'connection' => 'TiDB: local',
                    'alias' => $alias,
                    'prefix' => $prefix,
                    'tables' => [],
                ];
            }

            $tablesData = [];
            $tableCount = 0;

            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];

                // 除外テーブルをスキップ
                if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    continue;
                }

                $tablesData[$tableName] = [
                    'schema' => $this->getTableSchema($connection, $tableName),
                    'ddl' => $this->getCreateTableStatement($connection, $tableName),
                ];

                if ($tablesData[$tableName]['schema'] || $tablesData[$tableName]['ddl']) {
                    $tableCount++;
                }
            }

            $this->info("    完了: {$tableCount} テーブル");

            return [
                'connection' => 'TiDB: local',
                'alias' => $alias,
                'prefix' => $prefix,
                'tables' => $tablesData,
            ];
        } catch (\Exception $e) {
            $this->error("    エラー: " . $e->getMessage());
            return [
                'connection' => 'TiDB: local',
                'alias' => $alias,
                'prefix' => $prefix,
                'tables' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * SQLファイルに出力
     */
    private function writeSqlFile(string $outputDir, string $fileName, array $databases, string $title): void
    {
        $output = $this->generateHeader($title);

        foreach ($databases as $alias => $dbData) {
            $connection = $dbData['connection'] ?? '';
            $prefix = $dbData['prefix'] ?? '';
            $detail = $prefix ? "{$connection}, prefix: {$prefix}" : $connection;

            $output .= $this->generateSectionHeader($alias, $detail);

            if (isset($dbData['error'])) {
                $output .= "-- エラー: {$dbData['error']}\n\n";
                continue;
            }

            if (empty($dbData['tables'])) {
                $output .= "-- テーブルが見つかりません\n\n";
                continue;
            }

            foreach ($dbData['tables'] as $tableName => $tableData) {
                if (!empty($tableData['ddl'])) {
                    $output .= "-- Table: {$tableName}\n";
                    $output .= "{$tableData['ddl']};\n\n";
                }
            }
        }

        file_put_contents($outputDir . '/' . $fileName, $output);
        $this->outputFileInfo($outputDir . '/' . $fileName);
    }

    /**
     * YAMLファイルに出力
     */
    private function writeYmlFile(string $outputDir, string $fileName, array $data): void
    {
        $yamlContent = Yaml::dump($data, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        file_put_contents($outputDir . '/' . $fileName, $yamlContent);
        $this->outputFileInfo($outputDir . '/' . $fileName);
    }

    /**
     * JSONファイルに出力
     */
    private function writeJsonFile(string $outputDir, string $fileName, array $data): void
    {
        $jsonContent = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($outputDir . '/' . $fileName, $jsonContent);
        $this->outputFileInfo($outputDir . '/' . $fileName);
    }

    /**
     * DDLを除外してスキーマデータのみを抽出
     */
    private function extractSchemaData(array $databases): array
    {
        $result = [];

        foreach ($databases as $alias => $dbData) {
            $schemaDbData = [];

            if (isset($dbData['prefix'])) {
                $schemaDbData['prefix'] = $dbData['prefix'];
            }

            if (isset($dbData['error'])) {
                $schemaDbData['error'] = $dbData['error'];
                $schemaDbData['tables'] = [];
            } else {
                $schemaTables = [];
                foreach ($dbData['tables'] ?? [] as $tableName => $tableData) {
                    if (!empty($tableData['schema'])) {
                        $schemaTables[$tableName] = $tableData['schema'];
                    }
                }
                $schemaDbData['tables'] = $schemaTables;
            }

            $result[$alias] = $schemaDbData;
        }

        return $result;
    }

    /**
     * テーブルのスキーマ情報を取得
     */
    private function getTableSchema(string $connection, string $tableName): ?array
    {
        try {
            // テーブル基本情報を取得
            $tableInfo = $this->getTableInfo($connection, $tableName);

            // カラム情報を取得
            $columns = $this->getColumns($connection, $tableName);

            // インデックス情報を取得
            $indexes = $this->getIndexes($connection, $tableName);

            return [
                'comment' => $tableInfo['comment'] ?? '',
                'columns' => $columns,
                'indexes' => $indexes,
            ];
        } catch (\Exception $e) {
            $this->warn("      テーブル {$tableName} のスキーマ取得に失敗: " . $e->getMessage());
            return null;
        }
    }

    /**
     * テーブル基本情報を取得
     */
    private function getTableInfo(string $connection, string $tableName): array
    {
        $databaseName = config("database.connections.{$connection}.database");

        $result = DB::connection($connection)
            ->select(
                "SELECT ENGINE, TABLE_COLLATION, TABLE_COMMENT
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                [$databaseName, $tableName]
            );

        if (empty($result)) {
            return [];
        }

        $row = $result[0];
        $collation = $row->TABLE_COLLATION ?? '';
        $charset = '';

        // collationからcharsetを抽出
        if ($collation && str_contains($collation, '_')) {
            $charset = explode('_', $collation)[0];
        }

        return [
            'engine' => $row->ENGINE ?? '',
            'charset' => $charset,
            'collate' => $collation,
            'comment' => $row->TABLE_COMMENT ?? '',
        ];
    }

    /**
     * カラム情報を取得
     */
    private function getColumns(string $connection, string $tableName): array
    {
        $databaseName = config("database.connections.{$connection}.database");

        $results = DB::connection($connection)
            ->select(
                "SELECT
                    COLUMN_NAME,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_DEFAULT,
                    COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION",
                [$databaseName, $tableName]
            );

        $columns = [];
        foreach ($results as $row) {
            $columnName = $row->COLUMN_NAME;
            $columnData = [
                'type' => $this->normalizeColumnType($row->COLUMN_TYPE),
                'nullable' => $row->IS_NULLABLE === 'YES',
            ];

            if ($row->COLUMN_DEFAULT !== null) {
                $columnData['default'] = $row->COLUMN_DEFAULT;
            }

            if ($row->COLUMN_COMMENT) {
                $columnData['comment'] = $row->COLUMN_COMMENT;
            }

            $columns[$columnName] = $columnData;
        }

        return $columns;
    }

    /**
     * インデックス情報を取得
     */
    private function getIndexes(string $connection, string $tableName): array
    {
        $databaseName = config("database.connections.{$connection}.database");

        $results = DB::connection($connection)
            ->select(
                "SELECT
                    INDEX_NAME,
                    COLUMN_NAME,
                    NON_UNIQUE,
                    SEQ_IN_INDEX,
                    INDEX_TYPE
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY INDEX_NAME, SEQ_IN_INDEX",
                [$databaseName, $tableName]
            );

        $indexes = [];
        foreach ($results as $row) {
            $indexName = $row->INDEX_NAME;

            if (!isset($indexes[$indexName])) {
                // インデックスタイプを判定
                $type = 'index';
                if ($indexName === 'PRIMARY') {
                    $type = 'primary';
                } elseif ($row->NON_UNIQUE == 0) {
                    $type = 'unique';
                }

                $indexes[$indexName] = [
                    'type' => $type,
                    'columns' => [],
                ];

                if ($row->INDEX_TYPE) {
                    $indexes[$indexName]['index_type'] = $row->INDEX_TYPE;
                }
            }

            $indexes[$indexName]['columns'][] = $row->COLUMN_NAME;
        }

        return $indexes;
    }

    /**
     * CREATE TABLE文を取得
     */
    private function getCreateTableStatement(string $connection, string $tableName): ?string
    {
        try {
            $result = DB::connection($connection)
                ->select("SHOW CREATE TABLE `{$tableName}`");

            if (!empty($result)) {
                return $result[0]->{'Create Table'} ?? null;
            }
        } catch (\Exception $e) {
            $this->warn("      テーブル {$tableName} のDDL取得に失敗: " . $e->getMessage());
        }

        return null;
    }

    /**
     * ファイルヘッダーを生成
     */
    private function generateHeader(string $title): string
    {
        return <<<SQL
-- =============================================================================
-- {$title}
-- =============================================================================


SQL;
    }

    /**
     * セクションヘッダーを生成
     */
    private function generateSectionHeader(string $alias, string $detail): string
    {
        return <<<SQL

-- =============================================================================
-- Database: {$alias} ({$detail})
-- =============================================================================

SQL;
    }

    /**
     * ファイル情報を出力
     */
    private function outputFileInfo(string $outputPath): void
    {
        $fileSize = $this->formatFileSize(filesize($outputPath));
        $lineCount = count(file($outputPath));
        $fileName = basename($outputPath);
        $this->info("  → {$fileName} ({$fileSize}, {$lineCount}行)");
    }

    /**
     * ファイルサイズをフォーマット
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        $size = (float)$bytes;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * COLUMN_TYPEを正規化（環境差異を吸収）
     *
     * MySQLのバージョンや設定によって、整数型に表示幅が含まれる場合と含まれない場合がある
     * 例: tinyint(3) unsigned → tinyint unsigned
     *     int(11) → int
     *
     * この差異を吸収するため、整数型から表示幅を除去する
     */
    private function normalizeColumnType(string $columnType): string
    {
        // 整数型の表示幅を除去
        // 対象: tinyint, smallint, mediumint, int, bigint
        $columnType = preg_replace(
            '/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/i',
            '$1',
            $columnType
        );

        return $columnType;
    }
}
