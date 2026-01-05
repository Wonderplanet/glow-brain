<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Repositories\MngAssetReleaseVersionRepository;
use App\Domain\User\Constants\UserConstant;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Constants\AssetData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

class MngAssetReleaseVersionRepositoryTest extends TestCase
{
    private MngAssetReleaseVersionRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app()->make(MngAssetReleaseVersionRepository::class);
    }

    /**
     * プラットフォームのDataProvider
     *
     * @return array<string, array<int>>
     */
    public static function params_platform(): array
    {
        return [
            'iOS' => [UserConstant::PLATFORM_IOS],
            'Android' => [UserConstant::PLATFORM_ANDROID],
        ];
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_期間内のデータを取得(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash001',
                'platform' => $platform,
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 1000000,
                'catalog_byte_size' => 10000,
                'catalog_file_name' => 'catalog_001.json',
                'catalog_hash_file_name' => 'catalog_001.hash',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash002',
                'platform' => $platform,
                'build_client_version' => '1.0.1',
                'asset_total_byte_size' => 1100000,
                'catalog_byte_size' => 11000,
                'catalog_file_name' => 'catalog_002.json',
                'catalog_hash_file_name' => 'catalog_002.hash',
            ],
            [
                'id' => 'version_003',
                'release_key' => 1003,
                'git_revision' => 'ghi789',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash003',
                'platform' => $platform,
                'build_client_version' => '1.0.2',
                'asset_total_byte_size' => 1200000,
                'catalog_byte_size' => 12000,
                'catalog_file_name' => 'catalog_003.json',
                'catalog_hash_file_name' => 'catalog_003.hash',
            ],
        ]);

        // リリース情報を作成（期間内）
        MngAssetRelease::factory()->createMany([
            [
                'id' => 'release_001',
                'release_key' => 1001,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                'id' => 'release_002',
                'release_key' => 1002,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-12 00:00:00',
            ],
            [
                'id' => 'release_003',
                'release_key' => 1003,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_003',
                'client_compatibility_version' => '1.0.2',
                'start_at' => '2023-01-16 00:00:00', // 未来
            ],
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($platform, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(2, $result); // 期間内のデータのみ

        // release_keyの降順で並んでいることを確認
        $resultArray = $result->values()->toArray();
        $this->assertEquals(1002, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals(1001, $resultArray[1]['entity']->getReleaseKey());

        // client_compatibility_versionが含まれていることを確認
        $this->assertEquals('1.0.1', $resultArray[0]['client_compatibility_version']);
        $this->assertEquals('1.0.0', $resultArray[1]['client_compatibility_version']);
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_複数のrelease_keyでLIMIT適用(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // 5つのリリースバージョンを作成
        $releaseKeys = [1001, 1002, 1003, 1004, 1005];
        foreach ($releaseKeys as $key) {
            MngAssetReleaseVersion::factory()->create([
                'id' => 'version_' . $key,
                'release_key' => $key,
                'git_revision' => 'rev_' . $key,
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash_' . $key,
                'platform' => $platform,
                'build_client_version' => '1.0.' . ($key - 1000),
                'asset_total_byte_size' => 1000000 + ($key * 1000),
                'catalog_byte_size' => 10000 + ($key * 10),
                'catalog_file_name' => 'catalog_' . $key . '.json',
                'catalog_hash_file_name' => 'catalog_' . $key . '.hash',
            ]);

            MngAssetRelease::factory()->create([
                'id' => 'release_' . $key,
                'release_key' => $key,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_' . $key,
                'client_compatibility_version' => '1.0.' . ($key - 1000),
                'start_at' => '2023-01-10 00:00:00',
            ]);
        }

        // Exercise
        $result = $this->repository->getApplyCollection($platform, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(AssetData::ASSET_RELEASE_APPLY_LIMIT, $result);

        // 最新2つのrelease_keyのみ取得されていることを確認
        $resultArray = $result->values()->toArray();
        $this->assertEquals(1005, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals(1004, $resultArray[1]['entity']->getReleaseKey());
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_start_atがnullも含まれる(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngAssetReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => 1001,
            'git_revision' => 'abc123',
            'git_branch' => 'main',
            'catalog_hash' => 'catalog_hash001',
            'platform' => $platform,
            'build_client_version' => '1.0.0',
            'asset_total_byte_size' => 1000000,
            'catalog_byte_size' => 10000,
            'catalog_file_name' => 'catalog_001.json',
            'catalog_hash_file_name' => 'catalog_001.hash',
        ]);

        // start_atがnullのリリース情報を作成
        MngAssetRelease::factory()->create([
            'id' => 'release_001',
            'release_key' => 1001,
            'platform' => $platform,
            'enabled' => 1,
            'target_release_version_id' => 'version_001',
            'client_compatibility_version' => '1.0.0',
            'start_at' => null,
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($platform, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(1, $result);

        $resultArray = $result->values()->toArray();
        $this->assertEquals(1001, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals('1.0.0', $resultArray[0]['client_compatibility_version']);
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_無効なデータは除外(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash001',
                'platform' => $platform,
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 1000000,
                'catalog_byte_size' => 10000,
                'catalog_file_name' => 'catalog_001.json',
                'catalog_hash_file_name' => 'catalog_001.hash',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash002',
                'platform' => $platform,
                'build_client_version' => '1.0.1',
                'asset_total_byte_size' => 1100000,
                'catalog_byte_size' => 11000,
                'catalog_file_name' => 'catalog_002.json',
                'catalog_hash_file_name' => 'catalog_002.hash',
            ],
        ]);

        // リリース情報を作成（一部無効）
        MngAssetRelease::factory()->createMany([
            [
                'id' => 'release_001',
                'release_key' => 1001,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                'id' => 'release_002',
                'release_key' => 1002,
                'platform' => $platform,
                'enabled' => 0, // 無効
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-10 00:00:00',
            ],
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($platform, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(1, $result);

        $resultArray = $result->values()->toArray();
        $this->assertEquals(1001, $resultArray[0]['entity']->getReleaseKey());
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_target_release_version_idがnullの場合は除外(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngAssetReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => 1001,
            'git_revision' => 'abc123',
            'git_branch' => 'main',
            'catalog_hash' => 'catalog_hash001',
            'platform' => $platform,
            'build_client_version' => '1.0.0',
            'asset_total_byte_size' => 1000000,
            'catalog_byte_size' => 10000,
            'catalog_file_name' => 'catalog_001.json',
            'catalog_hash_file_name' => 'catalog_001.hash',
        ]);

        // target_release_version_idがnullのリリース情報を作成
        MngAssetRelease::factory()->create([
            'id' => 'release_001',
            'release_key' => 1001,
            'platform' => $platform,
            'enabled' => 1,
            'target_release_version_id' => null,
            'client_compatibility_version' => '1.0.0',
            'start_at' => '2023-01-10 00:00:00',
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($platform, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(0, $result);
    }

    #[DataProvider('params_platform')]
    public function test_getApplyCollection_未来の分もキャッシュされた上でキャッシュが機能する(int $platform): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngAssetReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash001',
                'platform' => $platform,
                'build_client_version' => '1.0.0',
                'asset_total_byte_size' => 1000000,
                'catalog_byte_size' => 10000,
                'catalog_file_name' => 'catalog_001.json',
                'catalog_hash_file_name' => 'catalog_001.hash',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash002',
                'platform' => $platform,
                'build_client_version' => '1.0.1',
                'asset_total_byte_size' => 1100000,
                'catalog_byte_size' => 11000,
                'catalog_file_name' => 'catalog_002.json',
                'catalog_hash_file_name' => 'catalog_002.hash',
            ],
            [
                'id' => 'version_003',
                'release_key' => 1003,
                'git_revision' => 'ghi789',
                'git_branch' => 'main',
                'catalog_hash' => 'catalog_hash003',
                'platform' => $platform,
                'build_client_version' => '1.0.2',
                'asset_total_byte_size' => 1200000,
                'catalog_byte_size' => 12000,
                'catalog_file_name' => 'catalog_003.json',
                'catalog_hash_file_name' => 'catalog_003.hash',
            ],
        ]);

        // リリース情報を作成
        MngAssetRelease::factory()->createMany([
            [
                // 期間内
                'id' => 'release_001',
                'release_key' => 1001,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                // 未来1
                'id' => 'release_002',
                'release_key' => 1002,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-16 00:00:00',
            ],
            [
                // 未来2
                'id' => 'release_003',
                'release_key' => 1003,
                'platform' => $platform,
                'enabled' => 1,
                'target_release_version_id' => 'version_003',
                'client_compatibility_version' => '1.0.2',
                'start_at' => '2023-01-17 00:00:00',
            ],
        ]);

        // sql発行回数
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // キャッシュはまだ存在しない
        $cache = $this->getFromRedis(CacheKeyUtil::getMngAssetReleaseVersionKey($platform));
        $this->assertNull($cache);

        // Exercise - 1回目の実行
        $result1 = $this->repository->getApplyCollection($platform, $now);
        // DBから取得している
        $this->assertEquals(2, $queryCount);

        // キャッシュが作成されていることを確認
        $cache = $this->getFromRedis(CacheKeyUtil::getMngAssetReleaseVersionKey($platform));
        $this->assertCount(3, $cache);
        $this->assertInstanceOf(MngAssetReleaseVersion::class, $cache->first());
        $this->assertEqualsCanonicalizing(
            ['version_003', 'version_002', 'version_001'],
            $cache->pluck('id')->toArray()
        );

        // Exercise - 2回目の実行（キャッシュから取得される）
        $result2 = $this->repository->getApplyCollection($platform, $now);
        // キャッシュから取得されているため、SQLは発行されない(カウントが変わらない)
        $this->assertEquals(2, $queryCount);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result1);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result2);
        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);

        // 同じデータが取得されることを確認
        $result1Array = $result1->values()->toArray();
        $result2Array = $result2->values()->toArray();
        $this->assertEquals($result1Array[0]['entity']->getReleaseKey(), $result2Array[0]['entity']->getReleaseKey());

        // Setup - 時間が進む
        $now = $this->fixTime('2023-01-16 15:00:00');

        // Exercise - 3回目の実行（キャッシュから取得される）
        $result3 = $this->repository->getApplyCollection($platform, $now);
        // キャッシュから取得されているため、SQLは発行されない(カウントが変わらない)
        $this->assertEquals(2, $queryCount);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result3);
        $this->assertCount(2, $result3);
        // release_keyの降順で並んでいることを確認
        $resultArray = $result3->values()->toArray();
        $this->assertEquals(1002, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals(1001, $resultArray[1]['entity']->getReleaseKey());

        // Setup - 時間が進む
        $now = $this->fixTime('2023-01-20 15:00:00');

        // Exercise - 4回目の実行（キャッシュから取得される）
        $result4 = $this->repository->getApplyCollection($platform, $now);
        // キャッシュから取得されているため、SQLは発行されない(カウントが変わらない)
        $this->assertEquals(2, $queryCount);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result4);
        $this->assertCount(2, $result4); // 定数で最大取得数2に設定されている
        // release_keyの降順で並んでいることを確認
        $resultArray = $result4->values()->toArray();
        $this->assertEquals(1003, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals(1002, $resultArray[1]['entity']->getReleaseKey());
    }
}
