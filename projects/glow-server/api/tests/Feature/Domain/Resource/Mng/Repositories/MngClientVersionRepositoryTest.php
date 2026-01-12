<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngClientVersionEntity;
use App\Domain\Resource\Mng\Models\MngClientVersion;
use App\Domain\Resource\Mng\Repositories\MngClientVersionRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;

class MngClientVersionRepositoryTest extends TestCase
{
    private MngClientVersionRepository $mngClientVersionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngClientVersionRepository = app()->make(MngClientVersionRepository::class);
    }

    /**
     * テスト用のクライアントバージョンデータを作成
     */
    private function createClientVersionMasterData(): void
    {
        // iOS版のデータ
        MngClientVersion::factory()->createMany([
            [
                'id' => 'cv_001',
                'client_version' => '1.0.0',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'is_force_update' => false,
            ],
            [
                'id' => 'cv_002',
                'client_version' => '1.1.0',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'is_force_update' => true,
            ],
            [
                'id' => 'cv_003',
                'client_version' => '1.2.0',
                'platform' => PlatformConstant::PLATFORM_IOS,
                'is_force_update' => false,
            ],
        ]);

        // Android版のデータ
        MngClientVersion::factory()->createMany([
            [
                'id' => 'cv_004',
                'client_version' => '1.0.0',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'is_force_update' => false,
            ],
            [
                'id' => 'cv_005',
                'client_version' => '1.1.0',
                'platform' => PlatformConstant::PLATFORM_ANDROID,
                'is_force_update' => true,
            ],
        ]);
    }

    public function test_findByVersion_キャッシュ動作確認(): void
    {
        // Setup
        $this->createClientVersionMasterData();
        $platform = PlatformConstant::PLATFORM_IOS;
        $cacheKey = CacheKeyUtil::getMngClientVersionKey($platform);

        // キャッシュが空であることを確認
        $this->assertNull($this->getFromRedis($cacheKey));

        // sql発行回数
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // Exercise 1 - 初回実行でキャッシュ作成
        $result1 = $this->mngClientVersionRepository->findByVersion('1.1.0', $platform);
        $this->assertNotNull($result1);
        $this->assertInstanceOf(MngClientVersionEntity::class, $result1);
        $this->assertEquals('1.1.0', $result1->getClientVersion());
        $this->assertEquals($platform, $result1->getPlatform());
        $this->assertTrue($result1->isRequireUpdate());

        // SQL発行回数が1回であることを確認
        $this->assertEquals(1, $queryCount);
        // キャッシュが作成されていることを確認
        $this->assertNotNull($this->getFromRedis($cacheKey));

        // Exercise 2 - 2回目実行でキャッシュから取得
        $result2 = $this->mngClientVersionRepository->findByVersion('1.2.0', $platform);
        $this->assertNotNull($result2);
        $this->assertEquals('1.2.0', $result2->getClientVersion());
        $this->assertFalse($result2->isRequireUpdate());

        // SQL発行回数が変わらないことを確認（キャッシュから取得）
        $this->assertEquals(1, $queryCount);

        // Exercise 3 - 存在しないバージョンの場合
        $result3 = $this->mngClientVersionRepository->findByVersion('2.0.0', $platform);
        $this->assertNull($result3);

        // SQL発行回数が変わらないことを確認（キャッシュから取得）
        $this->assertEquals(1, $queryCount);
    }

    public function test_findByVersion_異なるプラットフォーム(): void
    {
        // Setup
        $this->createClientVersionMasterData();

        // Exercise & Verify
        // iOS版
        $iosResult = $this->mngClientVersionRepository->findByVersion('1.1.0', PlatformConstant::PLATFORM_IOS);
        $this->assertNotNull($iosResult);
        $this->assertEquals(PlatformConstant::PLATFORM_IOS, $iosResult->getPlatform());
        $this->assertTrue($iosResult->isRequireUpdate());

        // Android版
        $androidResult = $this->mngClientVersionRepository->findByVersion('1.1.0', PlatformConstant::PLATFORM_ANDROID);
        $this->assertNotNull($androidResult);
        $this->assertEquals(PlatformConstant::PLATFORM_ANDROID, $androidResult->getPlatform());
        $this->assertTrue($androidResult->isRequireUpdate());

        // Android版のみに存在するバージョンをiOS版で検索
        $notFoundResult = $this->mngClientVersionRepository->findByVersion('1.2.0', PlatformConstant::PLATFORM_ANDROID);
        $this->assertNull($notFoundResult);
    }

    public function test_deleteAllCache(): void
    {
        // Setup
        $this->createClientVersionMasterData();

        // 各プラットフォームでキャッシュを作成
        $this->mngClientVersionRepository->findByVersion('1.1.0', PlatformConstant::PLATFORM_IOS);
        $this->mngClientVersionRepository->findByVersion('1.1.0', PlatformConstant::PLATFORM_ANDROID);

        $iosCacheKey = CacheKeyUtil::getMngClientVersionKey(PlatformConstant::PLATFORM_IOS);
        $androidCacheKey = CacheKeyUtil::getMngClientVersionKey(PlatformConstant::PLATFORM_ANDROID);

        // キャッシュが作成されていることを確認
        $this->assertNotNull($this->getFromRedis($iosCacheKey));
        $this->assertNotNull($this->getFromRedis($androidCacheKey));

        // Exercise: 全キャッシュ削除
        $this->mngClientVersionRepository->deleteAllCache();

        // Verify: 全キャッシュが削除されていることを確認
        $this->assertNull($this->getFromRedis($iosCacheKey));
        $this->assertNull($this->getFromRedis($androidCacheKey));
    }
}
