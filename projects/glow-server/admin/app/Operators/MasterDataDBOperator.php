<?php
namespace App\Operators;

use App\Constants\Database;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Opr\OprMasterReleaseControl;
use Illuminate\Support\Facades\Artisan;
use PDO;


class MasterDataDBOperator
{
    private ?string $connectionDbName = null;

    public function setCurrentMstConnection(): void
    {
        $newConfig = config("database.connections.api");
        $newConfig['database'] = $this->current(Database::TIDB_CONNECTION);

        config(["database.connections.mst" => $newConfig]);
    }

    // 接続先DBを設定する
    // null指定ならデフォルトに戻る
    public function setConnection(?string $connectionName): void
    {
        if (is_null($connectionName)) {
            $this->connectionDbName = null;
            return;
        }

        if (is_null(config("database.connections.${connectionName}"))) {
            // fadminホストと同じ接続設定でconnectionNameのデータベースに接続する設定を入れる
            $defaultConnection = config('database.default');
            $defaultConfig = config("database.connections.{$defaultConnection}");
            $newConfig = $defaultConfig;
            $newConfig['database'] = $connectionName;

            config(["database.connections.${connectionName}" => $newConfig]);
        }

        $this->connectionDbName = $connectionName;
    }

    // DBへの接続を取得する
    private function connection(): Connection
    {
        $db = is_null($this->connectionDbName) ? config('database.default') : $this->connectionDbName;
        return DB::connection($db);
    }

    public function showDatabases(): array
    {
        // 文字列情報しかいらないので一旦展開する
        return array_map(fn($dbObj) => $dbObj->Database, $this->connection()->select("show databases"));
    }

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

    public function showColumns(string $tableName): array
    {
        return $this->connection()->select('SHOW COLUMNS FROM ' . $tableName);
    }

    public function showPrimaryKeys(string $tableName): array
    {
        $keys = $this->connection()->select('SHOW KEYS FROM ' . $tableName . " WHERE Key_name = 'PRIMARY'");
        return array_map(function($d) { return $d->Column_name; }, $keys);
    }

    public function isExist($dbName) : bool
    {
        return in_array($dbName, $this->showDatabases());
    }

    public function isMasterDataDBName($dbName) : bool
    {
        return preg_match('/^'.config('app.env') .'_'.Database::MASTER_DATA_DB_PREFIX.'*/', $dbName);
    }

    public function create($dbName): void
    {
        if (!$this->isMasterDataDBName($dbName))
        {
            throw new Exception("cant create database. dbname is " . $dbName);
        }
        $this->connection()->statement("CREATE DATABASE IF NOT EXISTS " . $dbName);
    }

    public function drop($dbName): void
    {
        if (!$this->isExist($dbName))
        {
            throw new Exception("cant drop database. dbname " . $dbName . "is not exist.");
        }
        $this->connection()->statement("DROP DATABASE " . $dbName);
    }

    public function truncate($masterClass): void
    {
        $db = $this->connection()->getName();
        $masterClass::on($db)->truncate();
    }

    // 現在使用中のDB名
    public function currentDatabase() : string
    {
        $result = $this->connection()->select("select database() as dbName");
        return $result[0]->dbName;
    }

    // 日時から割り出した、現在有効なDB名
    // システムがこのDBに接続しているかどうかはわからないので注意
    // 使用中DBを確認するにはusing()メソッドで
    public function current(string $connectionName) : ?string
    {
        $targetDate = date('Y-m-d H:i:s');

        // release_controlの決定
        $releaseControl = OprMasterReleaseControl::on($connectionName)
            ->where('release_at', '<=', $targetDate )
            ->orderBy('release_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        if (is_null($releaseControl))
        {
            return null;
        }
        return $this->getMasterDbName($releaseControl->release_key .'_' . $releaseControl->git_revision);
    }

    public function migrate(string $dbName, string $migrationFilePath): void
    {
        $this->create($dbName);
        $this->setConnection($dbName);

        if (!in_array("migrations", $this->showTables()))
        {
            Artisan::call("migrate:install --database=${dbName}");
        }
        Artisan::call("migrate --force --path ${migrationFilePath} --database=${dbName}");
    }

    public function upsert($masterClass, array $data, array $primaryKeys = [])
    {
        $db = $this->connection()->getName();
        return $masterClass::on($db)->upsert($data, $primaryKeys);
    }

    public function copyDatabase(string $fromDbName, string $toDbName): void
    {
        $this->create($toDbName);

        $username = $this->connection()->getConfig()['username'];
        $password = $this->connection()->getConfig()['password'];
        $host = $this->connection()->getConfig()['host'];
        $port = $this->connection()->getConfig()['port'];
        $options = $this->connection()->getConfig()['options'];
        if (isset($options[PDO::MYSQL_ATTR_SSL_CA])) {
            $ssl = "--ssl-ca='" . $options[PDO::MYSQL_ATTR_SSL_CA] . "'";
        } else {
            $ssl = "";
        }

        $command = "mysqldump " . $ssl . " --skip-lock-tables -u ${username} -p'${password}' -h ${host} -P ${port} --no-data --ignore-table=${fromDbName}.migrations ${fromDbName} > dump.sql";
        $command .= " && mysqldump " . $ssl . " --skip-lock-tables -u ${username} -p'${password}' -h ${host} -P ${port} ${fromDbName} migrations >> dump.sql";
        $command .= " && mysql " . $ssl . " -u ${username} -p'${password}' -h ${host} -P ${port} ${toDbName} < dump.sql 2>&1";
        $command .= " && rm dump.sql";
        exec($command, $output, $return_var);

        if ($return_var !== 0)
        {
            throw new Exception("copy database failed. from ${fromDbName} to ${toDbName}: " . implode("\n", $output));
        }
    }

    public function getMasterDbName(string $version): string
    {
        return config('database.connections.mst.database');
    }
}
