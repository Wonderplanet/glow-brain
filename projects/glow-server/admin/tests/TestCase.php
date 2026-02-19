<?php

namespace Tests;

use App\Constants\Database;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;

    // api(local_test)のmigration実行ファイル
    private const API_MIGRATIONS_PATH = '../share/database/migrations/';

    protected array $connectionsToTransact = [
        Database::ADMIN_CONNECTION,
        Database::TIDB_CONNECTION,
        Database::MASTER_DATA_DB_PREFIX,
    ];

    /**
     * beforeRefreshingDatabaseでマイグレーションが実行されたかどうか
     *
     * @var boolean
     */
    private static $beforeRefreshMigrated = false;


    /**
     * teardown
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // テストごとにCarbonの日付をリセット
        Carbon::setTestNow(null);
    }

    /**
     * RefreshDatabase::beforeRefreshingDatabase() のオーバーライド
     *
     * @return void
     */
    protected function beforeRefreshingDatabase(): void
    {
        // beforeRefreshingDatabaseはsetupごとに実行されるので、
        // マイグレーションは通して一回のみの実行になるようにする
        if (!self::$beforeRefreshMigrated) {
            // api(local_test)側のmigrate:freshを実行
            $this->artisan('migrate:fresh', [
                '--database' => Database::TIDB_CONNECTION,
                '--path' => self::API_MIGRATIONS_PATH,
            ]);

            self::$beforeRefreshMigrated = true;
        }
    }
}
