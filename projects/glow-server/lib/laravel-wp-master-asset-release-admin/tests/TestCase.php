<?php

namespace WonderPlanet\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Tests\Support\Utils\ImportCsv;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;

    // api(local_test)のmigration実行ファイル
    private const API_MIGRATIONS_PATH = '../share/database/migrations/';

    /**
     * api(local_test)のschemaファイルのパス
     */
    private const API_SCHEMA_PATH = '../share/database/schema/tidb-schema.sql';

    /**
     * beforeRefreshingDatabaseでマイグレーションが実行されたかどうか
     *
     * @var boolean
     */
    private static $beforeRefreshMigrated = false;

    /**
     * デフォルトのCSVをインポートさせたく無い場合は、trueでオーバーライド
     *
     * @var bool
     */
    protected bool $ignoreDefaultCsvImport = false;

    /**
     * csvインポート用
     * model->factoryの呼び出しとmodelクラス再利用の効率化の為staticで定義
     *
     * @var ImportCsv
     */
    protected static ImportCsv $importCsv;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        // メモリ上限設定
        ini_set('memory_limit', '256M');
        parent::__construct($name, $data, $dataName);

        self::$importCsv = app()->make(ImportCsv::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // テスト毎に、テスト用データを作成
        if (!$this->ignoreDefaultCsvImport) {
            // csvインポート実行
            self::$importCsv->execCreateFixtureDataDefault();
        }
    }

    /**
     * teardown
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * テスト用のデータ作成
     * @var string $fixtureName fixture名
     * @var string $className クラス名
     *
     * @return void
     */
    protected function createFixtureData(string $fixtureName, string $className = ''): void
    {
        if (! $className) {
            $reflectionClass = new ReflectionClass(static::class);
            $className = $reflectionClass->getShortName();
        }
        // csvインポート実行
        self::$importCsv->execCreateFixtureData($className, $fixtureName);
    }
    
    /**
     * RefreshDatabase::connectionsToTransact() のオーバーライド
     *
     * @return array
     */
    protected function connectionsToTransact(): array
    {
        return [
            DBUtility::getAdminConnName(),
            DBUtility::getUsrConnName(),
            DBUtility::getMstConnName(),
        ];
    }
    
    /**
     * RefreshDatabase::beforeRefreshingDatabase() のオーバーライド
     *
     * @return void
     */
    protected function beforeRefreshingDatabase()
    {
        // beforeRefreshingDatabaseはsetupごとに実行されるので、
        // マイグレーションは通して一回のみの実行になるようにする
        if (!self::$beforeRefreshMigrated) {
            // api(local_test)側のmigrate:freshを実行
            $this->artisan('migrate:fresh', [
                '--database' => DBUtility::getUsrConnName(),
                '--path' => self::API_MIGRATIONS_PATH,
                '--schema-path' => self::API_SCHEMA_PATH,
            ]);
            
            self::$beforeRefreshMigrated = true;
        }
    }
}
