<?php

namespace WonderPlanet\Test\Feature\Domain\Cache\Services;

use App\Domain\Shop\Enums\ProductType;
use App\MasterResource\Models\Opr\OprProduct;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Cache\Services\RedisCacheService;
use WonderPlanet\Domain\Cache\Utils\RedisCacheUtility;

class RedisCacheServiceTest extends TestCase
{
    use RefreshDatabase;
    private RedisCacheService $redisCacheService;

    public function setUp(): void
    {
        parent::setUp();
        $this->redisCacheService = app(RedisCacheService::class);
    }

    #[Test]
    public function getRedisCacheAll_全件キャッシュされているか確認()
    {
        // Setup
        OprProduct::factory()->create([
            'id' => "test001"
        ]);
        OprProduct::factory()->create([
            'id' => "test002"
        ]);

        // Exercise
        $result = $this->redisCacheService->getRedisCacheAll(OprProduct::class);
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
            $this->redisCacheService,
            'createRedisCacheKey',
            [OprProduct::class, $sql]
        );
        $cache = RedisCacheUtility::getCache($cacheKey);

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
    public function getRedisCacheAll_キャッシュ削除フラグをONにした際の動作確認()
    {
        // Setup
        OprProduct::factory()->create([
            'id' => "test001"
        ]);
        OprProduct::factory()->create([
            'id' => "test002"
        ]);

        // Exercise
        $result = $this->redisCacheService->getRedisCacheAll(OprProduct::class);
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
            $this->redisCacheService,
            'createRedisCacheKey',
            [OprProduct::class, $sql]
        );
        $cache = RedisCacheUtility::getCache($cacheKey);

        // この時点でキャッシュが2件しかないこと
        $this->assertCount(2, $cache);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);

        // OprProductsにデータを追加
        // Setup
        OprProduct::factory()->create([
            'id' => "test003"
        ]);
        // Exercise
        $result = $this->redisCacheService->getRedisCacheAll(OprProduct::class, true);
        // Verify
        // returnが正しいこと
        $this->assertCount(3, $result);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test003";
        })->first();
        $this->assertNotNull($actual);

        $cache = RedisCacheUtility::getCache($cacheKey);

        // キャッシュが3件に更新されていること
        $this->assertCount(3, $cache);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test001";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test002";
        })->first();
        $this->assertNotNull($actual);
        $actual = $result->filter(function ($entity) {
            return $entity->getId() === "test003";
        })->first();
        $this->assertNotNull($actual);
    }

    #[Test]
    public function getRedisCacheByCustomSearchConditions_指定条件でキャッシュされているか確認()
    {
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
        $result = $this->redisCacheService->getRedisCacheByCustomSearchConditions($builder);
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

        $sql = $builder->toRawSql();
        $cacheKey = $this->callMethod(
            $this->redisCacheService,
            'createRedisCacheKey',
            [OprProduct::class, $sql]
        );
        $cache = RedisCacheUtility::getCache($cacheKey);

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
    public function getRedisCacheByCustomSearchConditionsWithCustomKey_作成したfunctionでキャッシュされているか確認()
    {
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
        $result = $this->redisCacheService->getRedisCacheByCustomSearchConditionsWithCustomKey(
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
            $this->redisCacheService,
            'createRedisCacheKey',
            [OprProduct::class, $customKey]
        );
        $cache = RedisCacheUtility::getCache($cacheKey);

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
    public function deleteReflogCache_指定したreflogキーのキャッシュが削除される確認()
    {
        // Setup
        $tempData = [1, 2, 3];
        $reflogKey = "reflog_key_test";
        $cacheKey1 = "cache_1";
        $cacheKey2 = "cache_2";
        RedisCacheUtility::saveCache($cacheKey1, $tempData);
        RedisCacheUtility::saveCache($cacheKey2, $tempData);
        $reflogData[$cacheKey1] = CarbonImmutable::now()->format('Y-m-d H:i:s');
        $reflogData[$cacheKey2] = CarbonImmutable::now()->format('Y-m-d H:i:s');
        RedisCacheUtility::saveCache($reflogKey, $reflogData);

        // Exercise
        $this->callMethod(
            $this->redisCacheService,
            'deleteReflogCache',
            [$reflogKey]
        );

        // Verify
        // キャッシュキーがないこと
        $actual = RedisCacheUtility::getCache($reflogKey);
        $this->assertNull($actual);
        $actual = RedisCacheUtility::getCache($cacheKey1);
        $this->assertNull($actual);
        $actual = RedisCacheUtility::getCache($cacheKey2);
        $this->assertNull($actual);
    }

    #[Test]
    public function createRedisCacheKey_キャッシュキー作成テスト_Redisキャッシュキー()
    {
        // Setup
        $modelClass = OprProduct::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->redisCacheService,
            'createRedisCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mng_opr_products:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
    }

    #[Test]
    public function createRedisReflogCacheKey_キャッシュキー作成テスト_Redisキャッシュキー()
    {
        // Setup
        $modelClass = OprProduct::class;
        $suffixKey = "test_suffix_key";
        $hashKey = md5($suffixKey);

        // Exercise
        $cacheKey = $this->callMethod(
            $this->redisCacheService,
            'createRedisReflogCacheKey',
            [$modelClass, $suffixKey]
        );

        // Verify
        $expectedKey = ":mng_ref:opr_products:" . $hashKey;
        $this->assertEquals($expectedKey, $cacheKey);
    }
}
