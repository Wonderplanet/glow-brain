<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use App\Constants\Database;
use App\Constants\DatalakeConstant;
use App\Constants\SystemConstants;
use App\Operators\S3Operator;
use App\Services\ConfigGetService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * TiDB dumpling実行サービス
 */
class TidbDumpService
{
    public function __construct(
        private ConfigGetService $configGetService,
        private S3Operator $s3Operator,
    ) {
    }

    /**
     * TiDBのusr/logデータを1テーブルずつ処理（dumpling→JSON変換→GCS転送）
     */
    public function dumpTidbTablesToJson(
        string $dbType,
        CarbonImmutable $targetDate,
        string $tempDisk,
        ?string $dayPath = null
    ): Collection {
        $processedFilesList = collect();

        Log::info("データレイク転送:dumpling実行開始:{$dbType}:{$targetDate->format('Y-m-d H:i:s')}");

        try {
            $tempStorage = Storage::disk($tempDisk);

            // 対象テーブル一覧を取得
            $tables = $this->getTargetTables($dbType);
            // TODO: デバッグ用
            // if ($dbType === 'usr') {
            //     $tables = collect(['usr_items']); // 例: usrデータのテーブル名
            // } elseif ($dbType === 'log') {
            //     $tables = collect(['log_items']); // 例: logデータのテーブル名
            // } else {
            //     throw new RuntimeException("不明なdbType: {$dbType}");
            // }

            foreach ($tables as $tableName) {
                Log::info("データレイク転送:テーブル処理開始:{$tableName}");

                // 1テーブル分の処理: dump → JSON変換 → GCS転送 → 一時ファイル削除
                $tableFileNames = $this->processSingleTableComplete(
                    $tableName,
                    $dbType,
                    $targetDate,
                    $tempStorage,
                    $dayPath
                );

                $processedFilesList = $processedFilesList->merge($tableFileNames);

                Log::info("データレイク転送:テーブル処理完了:{$tableName}");
            }

        } catch (\Exception $e) {
            Log::error("データレイク転送:dumpling実行エラー: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'db_type' => $dbType,
                'target_date' => $targetDate->format('Y-m-d H:i:s')
            ]);
            throw $e;
        }

        Log::info("データレイク転送:dumpling実行終了:{$dbType}");
        return $processedFilesList;
    }

    /**
     * 対象テーブル一覧を取得
     */
    private function getTargetTables(string $dbType): Collection
    {
        $tablePrefix = $dbType === 'usr' ? 'usr_' : 'log_';

        try {
            $tables = DB::connection(Database::TIDB_CONNECTION)
                ->select("SHOW TABLES LIKE '{$tablePrefix}%'");

            return collect($tables)->map(function ($table) {
                return array_values((array)$table)[0];
            });

        } catch (\Exception $e) {
            Log::error("テーブル一覧取得エラー: " . $e->getMessage(), [
                'db_type' => $dbType
            ]);
            throw $e;
        }
    }

    /**
     * 1テーブル分を完全に処理（dump → JSON変換 → GCS転送 → S3転送 → 一時ファイル削除）
     */
    private function processSingleTableComplete(
        string $tableName,
        string $dbType,
        CarbonImmutable $targetDate,
        $tempStorage,
        ?string $dayPath = null
    ): Collection {
        $uploadedFilesList = collect();

        try {
            // dumpling実行 → CSV.gz生成
            $csvFileNames = $this->dumpSingleTable($tableName, $dbType, $targetDate, $tempStorage);

            // CSV.gz → JSON変換
            $jsonFileNames = $this->convertDumpToJson($csvFileNames, $dbType, $targetDate, $tempStorage);

            // S3にCSV.gzファイルをアップロード（対象テーブルリストに含まれる場合のみ、一時ファイル削除前に実行）
            if ($this->shouldUploadToS3($tableName, $dbType)) {
                $this->uploadToS3($csvFileNames, $dbType, $targetDate);
            }

            // GCS転送 → 一時ファイル削除
            $uploadedFileNames = $this->uploadAndCleanup($jsonFileNames, $dbType, $targetDate, $tempStorage, $tableName, $dayPath);

            return $uploadedFileNames;

        } catch (\Exception $e) {
            Log::error("テーブル処理失敗: {$tableName} - " . $e->getMessage());

            // 失敗時も一時ファイルを可能な限り削除
            $this->cleanupTableFiles($tableName, $targetDate, $tempStorage);

            throw $e;
        }
    }

    /**
     * 単一テーブルをダンプしてCSV.gzに変換
     */
    private function dumpSingleTable(
        string $tableName,
        string $dbType,
        CarbonImmutable $targetDate,
        $tempStorage
    ): Collection {
        $outputDir = $tempStorage->path("dumpling_{$tableName}_" . $targetDate->format('Ymd_His'));
        $csvFilesList = collect();

        try {
            // 出力ディレクトリが存在しない場合は作成
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // dumplingの事前チェック・インストール
            $this->ensureDumplingInstalled();

            // GCS認証ファイルを作成（既存のfilesystems設定を使用）
            $credentialsFile = $this->getGcsCredentialsFile();

            // dumplingシェルスクリプトを実行（単一テーブル）
            $this->executeDumplingScript($tableName, $outputDir, $targetDate, $dbType, $credentialsFile);

            // 生成されたCSV.gzファイル一覧を取得
            $files = glob($outputDir . '/*.csv.gz');
            foreach ($files as $csvGzFile) {
                $basename = basename($csvGzFile);
                $csvFilesList->push([
                    'file_path' => $csvGzFile,
                    'table_name' => $tableName,
                    'basename' => $basename
                ]);
            }

            // GCS認証ファイルを削除（一時ファイルの場合のみ）
            if (isset($credentialsFile) && $this->isTemporaryCredentialsFile($credentialsFile)) {
                unlink($credentialsFile);
            }

            return $csvFilesList;

        } catch (\Exception $e) {
            // 一時ディレクトリが作成されている場合は削除
            if (isset($outputDir) && is_dir($outputDir)) {
                $this->cleanupDirectory($outputDir);
            }

            // GCS認証ファイルを削除（一時ファイルの場合のみ）
            if (isset($credentialsFile) && $this->isTemporaryCredentialsFile($credentialsFile)) {
                unlink($credentialsFile);
            }

            throw $e;
        }
    }

    /**
     * GCS認証ファイルを取得（既存のfilesystems設定を使用）
     */
    private function getGcsCredentialsFile(): string
    {
        $keyFile = config('filesystems.disks.datalake_gcs.key_file');

        if (empty($keyFile)) {
            throw new RuntimeException("filesystems.disks.datalake_gcs.key_fileが設定されていません");
        }

        // 配列の場合（json_decodeされた認証情報）は一時ファイルを作成
        if (is_array($keyFile)) {
            return $this->createTemporaryCredentialsFile($keyFile);
        }

        // 文字列の場合はファイルパスとして扱う
        if (is_string($keyFile) && file_exists($keyFile)) {
            return $keyFile;
        }

        throw new RuntimeException("GCS認証ファイルが見つからないか、不正な形式です: " . json_encode($keyFile));
    }

    /**
     * 一時的な認証ファイルを作成
     */
    private function createTemporaryCredentialsFile(array $credentialsData): string
    {
        // // 必要なフィールドをチェック
        // $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
        // foreach ($requiredFields as $field) {
        //     if (!isset($credentialsData[$field])) {
        //         throw new RuntimeException("GCS認証情報に必要なフィールドが不足しています: {$field}");
        //     }
        // }

        // 一時ファイルを作成
        $tempFile = tempnam(sys_get_temp_dir(), 'gcs_credentials_');
        if ($tempFile === false) {
            throw new RuntimeException("GCS認証ファイルの一時ファイル作成に失敗しました");
        }

        // JSONファイルとして書き出し
        if (file_put_contents($tempFile, json_encode($credentialsData, JSON_PRETTY_PRINT)) === false) {
            unlink($tempFile);
            throw new RuntimeException("GCS認証ファイルの書き込みに失敗しました");
        }

        // ファイル権限を制限（owner読み書きのみ）
        chmod($tempFile, 0600);


        return $tempFile;
    }

    /**
     * 一時ファイルかどうかを判定
     */
    private function isTemporaryCredentialsFile(string $filePath): bool
    {
        return strpos($filePath, sys_get_temp_dir()) === 0 && strpos($filePath, 'gcs_credentials_') !== false;
    }

    /**
     * ログ出力用にパスワードを隠したコマンド配列を作成
     */
    private function createLogSafeCommand(array $command): array
    {
        $logCommand = [];
        $hideNext = false;

        foreach ($command as $arg) {
            if ($hideNext) {
                // 前の引数が-pだった場合、パスワードを隠す
                $logCommand[] = '***';
                $hideNext = false;
            } elseif ($arg === '-p') {
                // -pオプションを発見
                $logCommand[] = $arg;
                $hideNext = true;
            } else {
                $logCommand[] = $arg;
            }
        }

        return $logCommand;
    }

    /**
     * dumplingがインストールされているかチェック・必要に応じてインストール
     */
    private function ensureDumplingInstalled(): void
    {
        $scriptPath = $this->getScriptPath('install_dumpling.sh');


        $this->executeShellProcess([$scriptPath], 300, 'dumpling', 'インストールチェック');

    }

    /**
     * dumplingシェルスクリプトを実行
     */
    private function executeDumplingScript(
        string $tableName,
        string $outputDir,
        CarbonImmutable $targetDate,
        string $dbType,
        ?string $credentialsFile = null
    ): void
    {
        $scriptPath = $this->getScriptPath('dump_tidb_table.sh');
        $command = $this->buildDumplingCommand($scriptPath, $tableName, $outputDir, $targetDate, $dbType, $credentialsFile);


        $this->executeShellProcess($command, 7200, $tableName, 'dumpling実行');

    }

    /**
     * CSV.gzファイルリストをJSONに並列高速変換
     */
    private function convertDumpToJson(
        Collection $csvFilesList,
        string $dbType,
        CarbonImmutable $targetDate,
        $tempStorage
    ): Collection {

        // 一時ディレクトリを作成
        $inputDir = $tempStorage->path('parallel_input_' . time());
        $outputDir = $tempStorage->path('parallel_output_' . time());

        mkdir($inputDir, 0755, true);
        mkdir($outputDir, 0755, true);

        try {
            // CSVファイルリストが空の場合は空のコレクションを返す
            if ($csvFilesList->isEmpty()) {
                Log::warning("CSVファイルリストが空のため、JSON変換をスキップします");
                return collect();
            }

            // 入力ファイルを一時ディレクトリにコピー
            foreach ($csvFilesList as $csvFileInfo) {
                $srcFile = $csvFileInfo['file_path'];
                $dstFile = $inputDir . '/' . $csvFileInfo['basename'];
                copy($srcFile, $dstFile);
            }

            // カラム情報を取得（最初のテーブルから）
            $firstTableName = $csvFilesList->first()['table_name'];
            $columns = $this->getTableColumns($firstTableName);
            $columnsJson = json_encode($columns);

            // CSV.gz → JSON.gz変換を並列実行
            $parallelScriptPath = $this->getScriptPath('convert_csv_gz_to_json_gz_parallel.sh');

            // 並列数を決定（CPUコア数の半分、最低2、最大8）
            $parallelJobs = max(2, min(8, intval(shell_exec('nproc') / 2)));

            $command = [
                'bash',
                $parallelScriptPath,
                $inputDir,
                $outputDir,
                $parallelJobs
            ];

            $this->executeShellProcess($command, 3600, 'parallel_conversion', 'CSV.gz → JSON.gz並列変換');

            // 変換されたファイルを収集
            $convertedFiles = collect();
            foreach ($csvFilesList as $csvFileInfo) {
                $basename = pathinfo($csvFileInfo['basename'], PATHINFO_FILENAME);
                $basename = pathinfo($basename, PATHINFO_FILENAME);
                $jsonGzFile = $outputDir . '/' . $basename . '.json.gz';
                if (file_exists($jsonGzFile)) {
                    $convertedFiles->push($jsonGzFile);
                }
            }

            // 変換結果を最終出力先に移動
            $jsonFilesList = collect();

            foreach ($convertedFiles as $index => $convertedFile) {
                if (file_exists($convertedFile)) {
                    $csvFileInfo = $csvFilesList->get($index);
                    $tableName = $csvFileInfo['table_name'];
                    $basename = pathinfo($csvFileInfo['basename'], PATHINFO_FILENAME);
                    $basename = pathinfo($basename, PATHINFO_FILENAME);

                    // パート番号を抽出
                    $partNumber = 0;
                    if (preg_match('/\.part(\d+)$/', $basename, $partMatches)) {
                        $partNumber = intval($partMatches[1]);
                    }

                    $jsonFileName = sprintf(
                        \App\Constants\DatalakeConstant::FILE_NAME_FORMAT,
                        $tableName,
                        $targetDate->format('Ymd'),
                        $partNumber
                    );
                    $jsonFileName .= '.gz';

                    $finalPath = $tempStorage->path($jsonFileName);
                    rename($convertedFile, $finalPath);

                    if (file_exists($finalPath)) {
                        $jsonFilesList->push($jsonFileName);
                    }
                }
            }

            return $jsonFilesList;

        } finally {
            // 一時ディレクトリを削除
            $this->cleanupDirectory($inputDir);
            $this->cleanupDirectory($outputDir);
        }
    }

    /**
     * JSON→GCS転送＆一時ファイル削除
     */
    private function uploadAndCleanup(
        Collection $jsonFilesList,
        string $dbType,
        CarbonImmutable $targetDate,
        $tempStorage,
        string $tableName,
        ?string $dayPath = null
    ): Collection {
        $uploadedFilesList = collect();

        // DatalakeServiceの圧縮・転送メソッドを利用
        $datalakeService = new \App\Services\Datalake\DatalakeService();

        // プレフィックス生成
        $prefixBase = $dbType === 'usr' ?
            \App\Constants\DatalakeConstant::GCS_PREFIX_USR :
            \App\Constants\DatalakeConstant::GCS_PREFIX_LOG;
        $env = config('app.env');
        $dbName = $this->getDbName($dbType);
        $pathPrefix = sprintf($prefixBase, $dbName, $env);

        $result = $datalakeService->compressAndUploadToGcs(
            $pathPrefix,
            $targetDate,
            $jsonFilesList,
            \App\Constants\DatalakeConstant::DISK_TEMP,
            \App\Constants\DatalakeConstant::DISK_GCS,
            $dayPath
        );

        if (!$result) {
            throw new RuntimeException("GCS転送に失敗しました: {$tableName}");
        }

        // 成功したファイル名を記録
        foreach ($jsonFilesList as $fileName) {
            $uploadedFilesList->push($fileName);
        }

        // このテーブルの一時ファイルを削除
        $this->cleanupTableFiles($tableName, $targetDate, $tempStorage);

        return $uploadedFilesList;
    }

    /**
     * ダミーデータを生成してJSON.gzファイルを作成
     */
    private function createDummyJsonFile(
        string $tableName,
        string $dbType,
        CarbonImmutable $targetDate,
        $tempStorage
    ): Collection {

        try {
            // テーブル構造を取得
            $tableColumns = $this->getTableStructure($tableName);

            if (empty($tableColumns)) {
                Log::warning("テーブル {$tableName} の構造が取得できません");
                return collect();
            }

            // テーブル構造に基づいてダミーデータを生成
            $dummyRecord = $this->generateDummyRecord($tableColumns, $tableName);
            $dummyData = [$dummyRecord];

            // JSONファイル名を生成（convertDumpToJsonの命名規則に合わせる）
            $jsonFileName = sprintf(
                \App\Constants\DatalakeConstant::FILE_NAME_FORMAT,
                $tableName,
                $targetDate->format('Ymd'),
                0 // パート番号は0
            );
            $jsonFileName .= '.gz';

            // JSON.gzファイルとして保存
            $filePath = $tempStorage->path($jsonFileName);
            $jsonContent = json_encode($dummyData, JSON_UNESCAPED_UNICODE);

            // gzで圧縮して保存
            $compressedData = gzencode($jsonContent);
            file_put_contents($filePath, $compressedData);


            return collect([$jsonFileName]);

        } catch (\Exception $e) {
            Log::error("ダミーデータ生成エラー: {$tableName} - " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }

    /**
     * テーブル構造を取得（DESCRIBE結果の拡張版）
     */
    private function getTableStructure(string $tableName): array
    {
        try {
            $columns = DB::connection(Database::TIDB_CONNECTION)
                ->select("DESCRIBE {$tableName}");

            $tableStructure = [];
            foreach ($columns as $column) {
                $tableStructure[] = [
                    'field' => $column->Field,
                    'type' => $column->Type,
                    'null' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra ?? ''
                ];
            }

            return $tableStructure;

        } catch (\Exception $e) {
            Log::error("テーブル構造取得エラー: {$tableName} - " . $e->getMessage());
            return [];
        }
    }

    /**
     * テーブル構造に基づいてダミーレコードを生成
     */
    private function generateDummyRecord(array $tableColumns, string $tableName): array
    {
        $record = [];

        foreach ($tableColumns as $column) {
            $fieldName = $column['field'];
            $fieldType = $column['type'];
            $isNullable = $column['null'];
            $extra = $column['extra'];

            // AUTO_INCREMENTフィールドはスキップ
            if (strpos($extra, 'auto_increment') !== false) {
                continue;
            }

            $record[$fieldName] = $this->generateFakeValueByColumnInfo($fieldType, $isNullable);
        }

        return $record;
    }

    /**
     * カラム情報に基づいてダミーデータを生成
     */
    private function generateFakeValueByColumnInfo(
        string $fieldType,
        bool $isNullable
    ): mixed {
        $fieldType = strtolower($fieldType);

        // NULLABLEの場合はnull
        if ($isNullable) {
            return null;
        }

        // データ型による固定値生成
        if (strpos($fieldType, 'varchar') !== false || strpos($fieldType, 'char') !== false) {
            return 'dummy_string';
        }

        if (strpos($fieldType, 'text') !== false) {
            return 'dummy_text_data';
        }

        if (strpos($fieldType, 'int') !== false) {
            return 1;
        }

        if (strpos($fieldType, 'decimal') !== false || strpos($fieldType, 'float') !== false || strpos($fieldType, 'double') !== false) {
            return 1.0;
        }

        if (strpos($fieldType, 'timestamp') !== false || strpos($fieldType, 'datetime') !== false) {
            return '2020-01-01 00:00:00';
        }

        if (strpos($fieldType, 'date') !== false) {
            return '2020-01-01';
        }

        if (strpos($fieldType, 'time') !== false) {
            return '00:00:00';
        }

        if (strpos($fieldType, 'json') !== false) {
            return '{"dummy": "data"}';
        }

        // デフォルト
        return 'dummy';
    }

    /**
     * テーブルのカラム情報を取得
     */
    private function getTableColumns(string $tableName): array
    {
        try {
            $columns = DB::connection(Database::TIDB_CONNECTION)
                ->select("DESCRIBE {$tableName}");

            return collect($columns)->pluck('Field')->toArray();

        } catch (\Exception $e) {
            Log::error("テーブルカラム情報取得エラー: " . $e->getMessage(), [
                'table_name' => $tableName
            ]);

            // デフォルトカラム情報を返す
            return ['id', 'created_at', 'updated_at'];
        }
    }

    /**
     * データベース名を取得
     */
    private function getDbName(string $dbType): string
    {
        if ($dbType === 'usr' || $dbType === 'log') {
            return config('database.connections.tidb.database');
        }

        throw new RuntimeException("不明なdbType: {$dbType}");
    }

    /**
     * 特定のテーブルに関連する一時ファイルを削除
     */
    private function cleanupTableFiles(
        string $tableName,
        CarbonImmutable $targetDate,
        $tempStorage
    ): void {
        try {
            // ダンプディレクトリを削除
            $dumpDirPattern = 'dumpling_' . $tableName . '_' . $targetDate->format('Ymd_His');
            $dumpDirPath = storage_path('app/datalake/' . $dumpDirPattern);

            if (is_dir($dumpDirPath)) {
                $this->cleanupDirectory($dumpDirPath);
            }

            // JSONファイルを削除（もし残っていれば）
            $jsonPattern = storage_path('app/datalake/' . $tableName . '-*.json');
            $jsonFiles = glob($jsonPattern);

            foreach ($jsonFiles as $jsonFile) {
                if (file_exists($jsonFile)) {
                    unlink($jsonFile);
                }
            }

        } catch (Throwable $e) {
            Log::warning("一時ファイル削除エラー({$tableName}): " . $e->getMessage());
        }
    }

    /**
     * ディレクトリを再帰的に削除
     */
    private function cleanupDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);

        foreach ($files as $file) {
            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->cleanupDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    // ================================
    // 共通メソッド
    // ================================

    /**
     * スクリプトパスを取得し、存在チェックを行う
     */
    private function getScriptPath(string $scriptName): string
    {
        $scriptPath = base_path("scripts/{$scriptName}");

        if (!file_exists($scriptPath)) {
            throw new RuntimeException("スクリプトが見つかりません: {$scriptPath}");
        }

        return $scriptPath;
    }

    /**
     * シェルプロセスを実行し、結果をハンドリング
     */
    private function executeShellProcess(
        array $command,
        int $timeout,
        string $contextName,
        string $operationName
    ): void {
        try {
            $result = Process::timeout($timeout)->run($command);

            if (!$result->successful()) {
                $errorMessage = $result->errorOutput() ?: $result->output();
                Log::error("{$operationName}エラー({$contextName}): {$errorMessage}");

                throw new RuntimeException(
                    "{$operationName}失敗({$contextName}): {$errorMessage}"
                );
            }

            // 成功時のログ出力（必要な場合のみ）
            $this->logProcessOutput($result, $contextName);

        } catch (Throwable $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }

            Log::error("{$operationName}例外({$contextName}): " . $e->getMessage());
            throw new RuntimeException(
                "{$operationName}例外({$contextName}): " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * プロセスの出力をログに記録（必要な情報のみ）
     */
    private function logProcessOutput($result, string $contextName): void
    {
        $output = trim($result->output());
        $errorOutput = trim($result->errorOutput());

        // エラーの場合のみログ出力（成功時の詳細ログは省略）
    }

    /**
     * 出力をログすべきか判定
     */
    private function shouldLogOutput(string $output): bool
    {
        // 空の場合はログしない
        if (empty($output)) {
            return false;
        }

        // 統計情報や完了メッセージのみログする
        $importantKeywords = ['完了', 'completed', '結果', 'result', '成功', 'success'];

        foreach ($importantKeywords as $keyword) {
            if (strpos($output, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * dumplingコマンドを構築
     */
    private function buildDumplingCommand(
        string $scriptPath,
        string $tableName,
        string $outputDir,
        CarbonImmutable $targetDate,
        string $dbType,
        ?string $credentialsFile
    ): array {
        $dbConfig = config('database.connections.tidb');

        $command = [
            $scriptPath,
            '-h', $dbConfig['host'],
            '-P', (string)$dbConfig['port'],
            '-u', $dbConfig['username'],
            '-p', $dbConfig['password'],
            '-d', $dbConfig['database'],
            '-t', $tableName,
            '-o', $outputDir,
        ];

        // logデータの場合は日付範囲指定
        if ($dbType === 'log') {
            /**
             * targetDateはJST。DBのcreated_atはUTC。
             * JSTで 00:00 - 23:59 の範囲を指定するには、UTCでは前日の15:00 - 当日の14:59となる。
             */
            $startDate = $targetDate->startOfDay()->setTimezone(SystemConstants::TIMEZONE_UTC)->format('Y-m-d H:i:s');
            $endDate = $targetDate->endOfDay()->setTimezone(SystemConstants::TIMEZONE_UTC)->format('Y-m-d H:i:s');

            if ($tableName === 'log_currency_frees') {
                /**
                 * 先方のGCSからBQへの取り込み時に、同じ列に、空文字と配列(と認識できるデータ)が混在しているとエラーになるらしい。
                 * log_currency_freesでは、空文字になるケースが1つだけあり、
                 * アカウント新規登録時の初期レコード作成時(pf_init)で発生するため、これを除外することで、
                 * 配列データのみに統一することができるため、エラーを回避できる。
                 * このレコードは、全ての値が0の初期化レコードであり、意味のないデータであるため、除外しても問題ない。
                 */
                $whereClause = "created_at >= '{$startDate}' AND created_at <= '{$endDate}' AND trigger_type != 'pf_init'";
            } else {
                $whereClause = "created_at >= '{$startDate}' AND created_at <= '{$endDate}'";
            }

            $command[] = '-w';
            $command[] = $whereClause;
        }

        // GCS認証ファイルがある場合は追加
        if ($credentialsFile) {
            $command[] = '-c';
            $command[] = $credentialsFile;
        }

        return $command;
    }


    /**
     * テーブルがS3アップロード対象かどうかを判定
     */
    private function shouldUploadToS3(string $tableName, string $dbType): bool
    {
        // ブラックリストチェック：除外対象テーブルの場合はfalse
        if (in_array($tableName, DatalakeConstant::WP_DATALAKE_S3_UPLOAD_BLACKLIST_TABLES, true)) {
            Log::info("WP_DATALAKE S3アップロード除外（ブラックリスト）: {$tableName}");
            return false;
        }

        // ホワイトリストチェック：対象テーブルリストに含まれている場合はtrue
        if (in_array($tableName, DatalakeConstant::WP_DATALAKE_S3_UPLOAD_TARGET_TABLES, true)) {
            Log::info("WP_DATALAKE S3アップロード対象（ホワイトリスト）: {$tableName}");
            return true;
        }

        // logDBの場合は従来通りアップロード対象（後方互換性のため）
        if ($dbType === 'log') {
            Log::info("WP_DATALAKE S3アップロード対象（logDB）: {$tableName}");
            return true;
        }

        // usrDBの場合はホワイトリストに明示的に含まれている場合のみ
        // （上記のホワイトリストチェックで既に判定済み）

        Log::info("WP_DATALAKE S3アップロード対象外: {$tableName} (dbType: {$dbType})");
        return false;
    }

    /**
     * CSV.gzファイルをS3にアップロード（WPデータレイク用）
     */
    private function uploadToS3(
        Collection $csvFileNames,
        string $dbType,
        CarbonImmutable $targetDate
    ): void {
        Log::info("WP_DATALAKE S3アップロード開始: {$dbType} ({$csvFileNames->count()}ファイル)");

        $config = $this->configGetService->getS3WpDatalake();

        $uploadedCount = 0;
        foreach ($csvFileNames as $csvFileInfo) {
            $localFilePath = $csvFileInfo['file_path'];
            $tableName = $csvFileInfo['table_name'];
            $fileName = $csvFileInfo['basename'];

            // S3キーを生成
            $s3Key = $this->generateWpDatalakeS3Key($tableName, $fileName, $targetDate);

            $this->s3Operator->putFromFileWithConfig($config, $localFilePath, $s3Key);
            $uploadedCount++;
        }

        Log::info("WP_DATALAKE S3アップロード完了: {$dbType} ({$uploadedCount}/{$csvFileNames->count()}ファイル成功)");
    }

    /**
     * WP_DATALAKE用S3キー(オブジェクトパス)を生成
     */
    private function generateWpDatalakeS3Key(
        string $tableName,
        string $fileName,
        CarbonImmutable $targetDate
    ): string {
        $dateStr = $targetDate->format('Y/m/d');

        return "raw/tidb/{$tableName}/{$dateStr}/{$fileName}";
    }
}
