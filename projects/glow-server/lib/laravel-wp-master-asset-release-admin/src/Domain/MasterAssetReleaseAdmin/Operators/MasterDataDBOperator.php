<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use WonderPlanet\Domain\Common\Utils\DBUtility;

/**
 * マスターデータインポートv2管理ツール用
 */
class MasterDataDBOperator
{
    private ?string $connectionDbName = null;

    private const STORAGE_PATH_DUMP_SQL = '/admin-tmp/';

    private const UPSERT_CHUNK_SIZE = 1000;

    private const STORAGE_PATH_MASTER_DATA_DUMP = '/masterdata_mysqldump/';

    public const MASTER_DATA_DB_PREFIX = 'mst_';

    /**
     * 接続先DBを設定する
     * null指定ならデフォルトに戻る
     *
     * @param string|null $connectionName
     * @return void
     */
    public function setConnection(?string $connectionName): void
    {
        if (is_null($connectionName)) {
            $this->connectionDbName = null;
            return;
        }

        if (is_null(config("database.connections.{$connectionName}"))) {
            // adminホストと同じ接続設定でconnectionNameのデータベースに接続する設定を入れる
            $defaultConnection = DBUtility::getDefaultMstConnName();
            $defaultConfig = config("database.connections.{$defaultConnection}");
            $newConfig = $defaultConfig;
            $newConfig['database'] = $connectionName;

            config(["database.connections.{$connectionName}" => $newConfig]);
        }

        $this->connectionDbName = $connectionName;
    }

    /**
     * DBへの接続を取得する
     *
     * @return Connection
     */
    private function connection(): Connection
    {
        $db = is_null($this->connectionDbName)
            ? DBUtility::getMstConnName()
            : $this->connectionDbName;
        return DB::connection($db);
    }

    /**
     * Mst DBのデフォルト接続を取得する
     *
     * リリース向けDBではなく、最新のマイグレーションが当たっている想定のDBとなる
     *
     * @return Connection
     */
    private function defaultMstConnection(): Connection
    {
        $db = DBUtility::getDefaultMstConnName();
        return DB::connection($db);
    }

    /**
     * @return array
     */
    public function showDatabases(): array
    {
        try {
            $databases = $this->connection()->select("show databases");
            return array_map(fn($dbObj) => $dbObj->Database, $databases);
        }
        catch (\PDOException $e) {
            \Log::error('MasterDataDBOperator showDatabases', [$e]);
            return [];
        }
    }

    /**
     * @return array
     */
    public function showTables(): array
    {
        // 文字列情報しかいらないので一旦展開する
        $tables = $this->connection()->select("show tables");
        if (count($tables) === 0)
        {
            return [];
        }
        // property名は動的に生成されているのでこういう感じに
        $prop = array_keys(get_object_vars($tables[0]))[0];
        return array_map(fn($dbObj) => $dbObj->{$prop}, $tables);
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function showColumns(string $tableName): array
    {
        return $this->connection()->select('SHOW COLUMNS FROM ' . $tableName);
    }

    /**
     * デフォルトMst接続でカラム情報を取得する
     *
     * @param string $tableName
     * @return array
     */
    public function showColumnsOnDefaultMst(string $tableName): array
    {
        return $this->defaultMstConnection()->select('SHOW COLUMNS FROM ' . $tableName);
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function showPrimaryKeys(string $tableName): array
    {
        $keys = $this->connection()->select('SHOW KEYS FROM ' . $tableName . " WHERE Key_name = 'PRIMARY'");
        return array_map(function($d) { return $d->Column_name; }, $keys);
    }

    /**
     * @param $dbName
     * @return bool
     */
    public function isExist($dbName) : bool
    {
        return in_array($dbName, $this->showDatabases());
    }

    /**
     * @param $dbName
     * @return bool
     */
    public function isMasterDataDBName($dbName) : bool
    {
        return preg_match('/^'.config('app.env') . '_' . self::MASTER_DATA_DB_PREFIX . '*/', $dbName);
    }

    /**
     * @param $dbName
     * @return void
     * @throws Exception
     */
    public function create($dbName): void
    {
        if (!$this->isMasterDataDBName($dbName))
        {
            throw new Exception("can not create database. dbname is " . $dbName);
        }
        $this->connection()->statement("CREATE DATABASE IF NOT EXISTS " . $dbName);
    }

    /**
     * @param string $dbName
     * @return void
     */
    public function drop(string $dbName): void
    {
        if (!$this->isExist($dbName)) {
            // 対象DBが存在しない場合は何もしない
            return;
        }
        $this->connection()->statement("DROP DATABASE " . $dbName);
    }

    /**
     * @param $masterClass
     * @return void
     */
    public function truncate($masterClass): void
    {
        $db = $this->connection()->getName();
        $masterClass::on($db)->truncate();
    }

    /**
     * @param string $dbName
     * @param string $migrationFilePath
     * @return void
     * @throws Exception
     */
    public function migrate(string $dbName, string $migrationFilePath): void
    {
        $this->create($dbName);
        $this->setConnection($dbName);

        if (!in_array("migrations", $this->showTables())) {
            Artisan::call("migrate:install --database={$dbName}");
        }
        Artisan::call("migrate --force --path {$migrationFilePath} --database={$dbName}");
    }

    /**
     * @param $masterClass
     * @param array $data
     * @param array $primaryKeys
     * @return void
     */
    public function upsert($masterClass, array $data, array $primaryKeys = []): void
    {
        \Log::info('upsert start: ' . get_class($masterClass));
        $db = $this->connection()->getName();

        // 「1390 Prepared statement contains too many placeholders」エラー回避のため、分割して実行する
        foreach (array_chunk($data, self::UPSERT_CHUNK_SIZE) as $chunkData) {
            $masterClass::on($db)->upsert($chunkData, $primaryKeys);
        }
    }

    /**
     * 接続情報取得
     *
     * @return array<string, string>
     */
    private function getConfig(): array
    {
        $username = $this->connection()->getConfig()['username'];
        $password = $this->connection()->getConfig()['password'];
        $host = $this->connection()->getConfig()['host'];
        $port = $this->connection()->getConfig()['port'];
        $options = $this->connection()->getConfig()['options'];
        $ssl = "";
        if (isset($options[PDO::MYSQL_ATTR_SSL_CA])) {
            $ssl = "--ssl-ca='" . $options[PDO::MYSQL_ATTR_SSL_CA] . "'";
        }

        return [
            'userName' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'ssl' => $ssl,
        ];
    }

    /**
     * @param string $fromDbName
     * @param string $toDbName
     * @return void
     * @throws Exception
     */
    public function copyDatabase(string $fromDbName, string $toDbName): void
    {
        $this->create($toDbName);
        [
            'userName' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'ssl' => $ssl,
        ] = $this->getConfig();

        // $fromDbNameのテーブル構造をdump.sqlに保存
        $this->createDumpDirectory(self::STORAGE_PATH_DUMP_SQL);
        $filepath = $this->getDumpDirectoryPath("dump_{$toDbName}.sql");
        $command = "mysqldump " . $ssl . " --skip-lock-tables --set-gtid-purged=OFF -u {$username} -p'{$password}' -h {$host} -P {$port} --no-data --ignore-table={$fromDbName}.migrations {$fromDbName} > {$filepath}";
        // $fromDbNameのmigrationsテーブルの構造とデータをdump.sqlへ更新
        $command .= " && mysqldump " . $ssl . " --skip-lock-tables --set-gtid-purged=OFF -u {$username} -p'{$password}' -h {$host} -P {$port} {$fromDbName} migrations >> {$filepath}";
        // $toDbName向けにdump.sqlを実行
        $command .= " && mysql " . $ssl . " -u {$username} -p'{$password}' -h {$host} -P {$port} {$toDbName} < {$filepath} 2>&1";
        // dump.sqlを削除
        $command .= " && rm {$filepath}";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new Exception("copy database failed. from {$fromDbName} to {$toDbName}: return: {$return_var} output: " . implode("\n", $output));
        }
    }

    /**
     * マスタースキームバージョンを取得する
     *
     * @param $dbName
     * @return string
     * @throws Exception
     */
    public function getMasterSchemaVersion($dbName): string
    {
        [
            'userName' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'ssl' => $ssl,
        ] = $this->getConfig();

        // $dbNameのテーブル構造をdump.sqlに保存
        $this->createDumpDirectory(self::STORAGE_PATH_DUMP_SQL);
        $filepath = $this->getDumpDirectoryPath("dump_hush_{$dbName}.sql");
        $command = "mysqldump " . $ssl . " --skip-lock-tables -u {$username} -p'{$password}' -h {$host} -P {$port} --no-data --ignore-table={$dbName}.migrations {$dbName} > {$filepath}";
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            throw new Exception("create dump_hush_{$dbName}.sql failed. : " . implode("\n", $output));
        }
        // dump.sqlのファイルハッシュ値を取得
        $masterSchemaVersion = md5_file($filepath);
        // dump.sqlを削除
        exec("rm {$filepath}");

        return $masterSchemaVersion;
    }

    /**
     * 与えられたパラメータを元に環境名を接頭辞に含めたマスターDB名を生成する
     *
     * @param string $releaseKey
     * @param array $serverDbHashMap
     * @return string
     */
    public function getMasterDbName(string $releaseKey, array $serverDbHashMap): string
    {
        // $versionごとのDB名を生成する
        return config('app.env') . '_' . $this->getMasterDbNameNoPrefix($releaseKey, $serverDbHashMap);
    }

    /**
     * 接頭辞の環境名以外のDB名を生成する
     * (別の処理で接頭辞なしのDB名が必要だったので処理を分離)
     *
     * @param string $releaseKey
     * @param array $serverDbHashMap
     * @return string
     */
    public function getMasterDbNameNoPrefix(string $releaseKey, array $serverDbHashMap): string
    {
        $serverDbHash = $serverDbHashMap[$releaseKey];
        $version = "{$releaseKey}_{$serverDbHash}";

        return self::MASTER_DATA_DB_PREFIX . $version;
    }

    /**
     * dumpファイル配置用のディレクトを作成
     *
     * @param string $dirName
     * @return void
     */
    private function createDumpDirectory(string $dirName): void
    {
        $disk = Storage::disk('local');
        if (!$disk->exists($dirName)) {
            // 一時保存用のディレクトリがなければ作成する
            $disk->makeDirectory($dirName);
        }
    }

    /**
     * dumpファイル保存用ディレクトリのパスを取得
     *
     * @param string $fileName
     * @return string
     */
    private function getDumpDirectoryPath(string $fileName): string
    {
        // ディレクトリパスを返す
        return Storage::disk('local')->path(self::STORAGE_PATH_DUMP_SQL . $fileName);
    }

    /**
     * 対象マスターDBのdumpファイルを作成する
     * ※対象はインポート済みのマスターDBであること
     *
     * @param string $importId
     * @param string $releaseKey
     * @param array $serverDbHashMap
     * @return void
     * @throws Exception
     */
    public function generateMasterDBDump(string $importId, string $releaseKey, array $serverDbHashMap): void
    {
        [
            'userName' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'ssl' => $ssl,
        ] = $this->getConfig();

        // dumpファイル保存用のディレクトリ作成
        $storagePath = self::STORAGE_PATH_MASTER_DATA_DUMP . "/{$importId}/{$releaseKey}/";
        $this->createDumpDirectory($storagePath);

        // 対象マスターDBのmysqldumpを実行
        $dbName = $this->getMasterDbName($releaseKey, $serverDbHashMap);
        $baseFilePath = config('wp_master_asset_release_admin.masterDataMysqlDump');
        $filepath = "{$baseFilePath}/{$importId}/{$releaseKey}/{$dbName}.sql";
        $command = "mysqldump " . $ssl . " --skip-lock-tables --set-gtid-purged=OFF -u {$username} -p'{$password}' -h {$host} -P {$port} {$dbName} > {$filepath}";
        exec($command, $output, $return_var);
        if ($return_var !== 0) {
            throw new Exception("create {$dbName}.sql failed. : " . implode("\n", $output));
        }
    }

    /**
     * 指定したmysqldumpファイルでDBを作成する
     *
     * @param string $dbName
     * @param string $mysqlDumpFilePath
     * @return void
     * @throws Exception
     */
    public function copyDatabaseFromFilepath(string $dbName, string $mysqlDumpFilePath): void
    {
        $this->create($dbName);
        [
            'userName' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'ssl' => $ssl,
        ] = $this->getConfig();

        $baseFilePath = storage_path('app/' . $mysqlDumpFilePath);
        // $dbName向けにdump.sqlを実行
        $command = "mysql " . $ssl . " -u {$username} -p'{$password}' -h {$host} -P {$port} {$dbName} < {$baseFilePath} 2>&1";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new Exception("copy database failed. from {$dbName} : return: {$return_var} output: " . implode("\n", $output));
        }
    }
}
