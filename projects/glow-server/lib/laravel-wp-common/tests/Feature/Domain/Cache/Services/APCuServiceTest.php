<?php

namespace WonderPlanet\Test\Feature\Domain\Cache\Services;

use App\Domain\Shop\Enums\ProductType;
use App\MasterResource\Models\Opr\OprGachaOverridePickupRate;
use App\MasterResource\Models\Opr\OprProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Cache\Services\APCuService;
use WonderPlanet\Domain\Cache\Utils\APCuUtility;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

/**
 * WARNING: テスト実行時、docker/php/Dockerfileに下記のようにapc.enable_cliを有効後、スキップ処理を削除して手元で動かしてください
 * 「RUN echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini」
 * 参考:
 * https://github.com/Wonderplanet/laravel-wp-framework/pull/1157/commits/f57a679cedb97403b1ea88b2384106e4e6305023#diff-76eca0a88af23af10f01cf6f3eac88eb29d73426ef393131b2e9b525d7d57b38R82
 */
class APCuServiceTest extends TestCase
{
    use RefreshDatabase;
    private APCuService $apcuService;
    
    private string $configDatabaseConnectionsMstDatabase = '';
    
    protected $backupConfigKeys = [
        'database.connections.mst.database',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->apcuService = app(APCuService::class);

        // テストのために書き換え
        Config::set('database.connections.mst.database', 'testing_mst_202301_serverDbHash');
    }

    public function tearDown(): void
    {
        // テスト実行でエラーになる為、テストごとに設定したキャッシュを削除
        Cache::store('apc')->clear();
        parent::tearDown();
    }

    #[Test]
    public function getAll_全件キャッシュされているか確認()
    {
        $this->markTestSkipped('CLI設定を設定してからテストを実行する必要があるためスキップ');
        // Setup
        OprProduct::factory()->create([
            'id' => "test001"
        ]);
        OprProduct::factory()->create([
            'id' => "test002"
        ]);
        $mngMasterReleaseVersionId = 'version-1';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);

        // Exercise
        $result = $this->apcuService->getAll(OprProduct::class);
        // Verify
        // returnが正しいこと
        $this->assertCount(2, $result);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);

        $sql = "select * from `opr_products`";
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createCacheKey',
            [OprProduct::class, $sql]
        );

        $cache = APCuUtility::getCache($cacheKey);

        // キャッシュデータが正しいこと
        $this->assertCount(2, $cache);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
    }

    #[Test]
    public function getByCustomSearchConditions_指定条件でキャッシュされているか確認()
    {
        $this->markTestSkipped('CLI設定を設定してからテストを実行する必要があるためスキップ');
        // Setup
        OprProduct::factory()->create([
            'id' => "test001",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test002",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test003",
            'product_type' => ProductType::DIAMOND->value,
        ]);
        $builder = OprProduct::query()->where('product_type', ProductType::MONTHLY_PASS->value);

        // Exercise
        $result = $this->apcuService->getByCustomSearchConditions($builder);
        // Verify
        // returnが正しいこと
        $this->assertCount(2, $result);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);

        $sql = "select * from `opr_products` where `product_type` = 'MonthlyPass'";
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createCacheKey',
            [OprProduct::class, $sql]
        );

        $cache = APCuUtility::getCache($cacheKey);
        // キャッシュデータが正しいこと
        $this->assertCount(2, $cache);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
    }

    #[Test]
    public function getByCustomSearchConditionsWithCustomKey_作成したfunctionでキャッシュされているか確認()
    {
        $this->markTestSkipped('CLI設定を設定してからテストを実行する必要があるためスキップ');
        // Setup
        OprProduct::factory()->create([
            'id' => "test001",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test002",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test003",
            'product_type' => ProductType::DIAMOND->value,
        ]);
        $customKey = "product_type";

        // Exercise
        $result = $this->apcuService->getByCustomSearchConditionsWithCustomKey(
            OprProduct::class,
            $customKey,
            function () {
                $result = OprProduct::query()->get();
                return $result->groupBy('product_type');
            },
        );

        // Verify
        // returnが正しいこと
        $this->assertCount(2, $result);
        foreach ($result as $key => $values) {
            if ($key === ProductType::DIAMOND->value) {
                $this->assertCount(1, $values);
                $actual = collect($values)->first();
                $this->assertEquals("test003", $actual['id']);
            } elseif ($key === ProductType::MONTHLY_PASS->value) {
                $this->assertCount(2, $values);
                $actual = collect($values)->filter(function ($entity) {
                    return $entity['id'] === "test001";
                })->first();
                $this->assertNotNull($actual);
                $actual = collect($values)->filter(function ($entity) {
                    return $entity['id'] === "test002";
                })->first();
                $this->assertNotNull($actual);
            } else {
                $this->fail();
            }
        }

        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createCacheKey',
            [OprProduct::class, $customKey]
        );

        // キャッシュが取得できること
        $cache = APCuUtility::getCache($cacheKey);
        // キャッシュデータが正しいこと
        foreach ($cache as $key => $values) {
            if ($key === ProductType::DIAMOND->value) {
                $this->assertCount(1, $values);
                $actual = collect($values)->first();
                $this->assertEquals("test003", $actual['id']);
            } elseif ($key === ProductType::MONTHLY_PASS->value) {
                $this->assertCount(2, $values);
                $actual = collect($values)->filter(function ($entity) {
                    return $entity['id'] === "test001";
                })->first();
                $this->assertNotNull($actual);
                $actual = collect($values)->filter(function ($entity) {
                    return $entity['id'] === "test002";
                })->first();
                $this->assertNotNull($actual);
            } else {
                $this->fail();
            }
        }
    }

    #[Test]
    public function getCacheAndCreateCache_渡した関数でデータを取得するかテスト_全件取得()
    {
        // Setup
        // 配信中データ
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => 'version-1',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => 'version-1',
                'release_key' => '202301',
                'git_revision' => 'test',
                'server_db_hash' => 'serverDbHash',
            ]);
        OprProduct::factory()->create([
            'id' => "test001",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test002",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test003",
            'product_type' => ProductType::DIAMOND->value,
        ]);
        $builder = OprProduct::query();

        // Exercise
        $result = $this->callMethod(
            $this->apcuService,
            'getCacheAndCreateCache',
            [
                OprProduct::class,
                "test_key",
                function () use ($builder) {
                    $collection = $builder->get();
                    return $collection->map(function ($row) {
                        return $row->toEntity();
                    });
                }
            ]
        );

        // Verify
        // returnが正しいこと
        $this->assertCount(3, $result);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $this->assertEquals(ProductType::MONTHLY_PASS->value, $actual->getProductType());
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
        $this->assertEquals(ProductType::MONTHLY_PASS->value, $actual->getProductType());
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test003";
        })->first();
        $this->assertNotNull($actual);
        $this->assertEquals(ProductType::DIAMOND->value, $actual->getProductType());
    }

    #[Test]
    public function getCacheAndCreateCache_渡した関数でデータを取得するかテスト_任意の検索条件()
    {
        // Setup
        // 配信中データ
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => 'version-1',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => 'version-1',
                'release_key' => '202301',
                'git_revision' => 'test',
                'server_db_hash' => 'serverDbHash',
            ]);
        OprProduct::factory()->create([
            'id' => "test001",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test002",
            'product_type' => ProductType::MONTHLY_PASS->value,
        ]);
        OprProduct::factory()->create([
            'id' => "test003",
            'product_type' => ProductType::DIAMOND->value,
        ]);
        $builder = OprProduct::query()->where('product_type', ProductType::MONTHLY_PASS->value);

        // Exercise
        $result = $this->callMethod(
            $this->apcuService,
            'getCacheAndCreateCache',
            [
                OprProduct::class,
                "test_key",
                function () use ($builder) {
                    $collection = $builder->get();
                    return $collection->map(function ($row) {
                        return $row->toEntity();
                    });
                }
            ]
        );

        // Verify
        // returnが正しいこと
        $this->assertCount(2, $result);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
    }

    #[Test]
    public function createCacheKey_キャッシュキー作成テスト_APCuキー()
    {
        $this->markTestSkipped('APCuService::createCacheKey内のenv("APP_ENV") === "testing"処理を消した上で実行する必要があるためスキップ');
        // Setup
        // MasterRepository::createCacheKey内のenv('APP_ENV') === 'testing'処理を消した上で実行すること
        $mngMasterReleaseVersionId = 'version-1';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);
        $dbMane = $mngMasterReleaseVersion->toEntity()->getDbName();

        $modelClass = OprProduct::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($dbMane . '_' . $suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mst_opr_products:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
        $this->assertLessThanOrEqual(60, mb_strlen($cacheKey));
    }

    #[Test]
    public function createCacheKey_キャッシュキーで文字数が削られる()
    {
        $this->markTestSkipped('APCuService::createCacheKey内のenv("APP_ENV") === "testing"処理を消した上で実行する必要があるためスキップ');
        // Setup
        // MasterRepository::createCacheKey内のenv('APP_ENV') === 'testing'処理を消した上で実行すること
        $mngMasterReleaseVersionId = 'version-1';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);
        $dbMane = $mngMasterReleaseVersion->toEntity()->getDbName();
        $modelClass = OprGachaOverridePickupRate::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($dbMane . '_' . $suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mst_opr_gacha_override_pic:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
        $this->assertEquals(60, mb_strlen($cacheKey));
    }

    #[Test]
    public function createReflogCacheKey_キャッシュキー作成テスト_APCuキー()
    {
        $this->markTestSkipped('APCuService::createCacheKey内のenv("APP_ENV") === "testing"処理を消した上で実行する必要があるためスキップ');
        // Setup
        // MasterRepository::createCacheKey内のenv('APP_ENV') === 'testing'処理を消した上で実行すること
        $modelClass = OprProduct::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createReflogCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mst_ref:opr_products:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
        $this->assertLessThanOrEqual(60, mb_strlen($cacheKey));
    }

    #[Test]
    public function createReflogCacheKey_キャッシュキーで文字数が削られる()
    {
        $this->markTestSkipped('APCuService::createCacheKey内のenv("APP_ENV") === "testing"処理を消した上で実行する必要があるためスキップ');
        // Setup
        // MasterRepository::createCacheKey内のenv('APP_ENV') === 'testing'処理を消した上で実行すること
        $modelClass = OprGachaOverridePickupRate::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->apcuService,
            'createReflogCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mst_ref:opr_gacha_override:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
        $this->assertEquals(60, mb_strlen($cacheKey));
    }
    
    #[Test]
    public function getReleaseVersionDbNames_データ取得チェック(): void
    {
        // Setup
        $mngMasterReleaseVersionId2 = 'version-2';
        // 配信中データ(最新)
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202302',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId2,
            ]);
        $mngMasterReleaseVersion = MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId2,
                'release_key' => '202302',
                'git_revision' => 'test2',
                'server_db_hash' => 'serverDbHash2',
            ]);
        // 配信中データ(最古)
        $mngMasterReleaseVersionId1 = 'version-1';
        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId1,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId1,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash1',
            ]);

        // Exercise
        $reflection = new \ReflectionClass($this->apcuService);
        $method = $reflection->getMethod('getReleaseVersionDbNames');
        $method->setAccessible(true);
        $actuals = $method->invoke($this->apcuService);

        // Verify
        // APCuService内で使用するパラメータを比較して一致するか確認
        $expected = $mngMasterReleaseVersion->toEntity();
        $this->assertEquals($expected->getDbName(), $actuals[0]);
    }
}
