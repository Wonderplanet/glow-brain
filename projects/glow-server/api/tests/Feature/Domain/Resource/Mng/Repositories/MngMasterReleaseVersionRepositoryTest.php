<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Repositories\MngMasterReleaseVersionRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MngMasterReleaseVersionRepositoryTest extends TestCase
{
    private MngMasterReleaseVersionRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app()->make(MngMasterReleaseVersionRepository::class);
    }

    public function test_getApplyCollection_期間内のデータを取得(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngMasterReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'master_schema_version' => '1.0.0',
                'data_hash' => 'hash001',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'master_schema_version' => '1.0.1',
                'data_hash' => 'hash002',
            ],
            [
                'id' => 'version_003',
                'release_key' => 1003,
                'git_revision' => 'ghi789',
                'master_schema_version' => '1.0.2',
                'data_hash' => 'hash003',
            ],
        ]);

        // リリース情報を作成（期間内）
        MngMasterRelease::factory()->createMany([
            [
                'id' => 'release_001',
                'release_key' => 1001,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                'id' => 'release_002',
                'release_key' => 1002,
                'enabled' => 1,
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-12 00:00:00',
            ],
            [
                'id' => 'release_003',
                'release_key' => 1003,
                'enabled' => 1,
                'target_release_version_id' => 'version_003',
                'client_compatibility_version' => '1.0.2',
                'start_at' => '2023-01-16 00:00:00', // 未来
            ],
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($now);

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

    public function test_getApplyCollection_複数のrelease_keyでLIMIT適用(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // 5つのリリースバージョンを作成
        $releaseKeys = [1001, 1002, 1003, 1004, 1005];
        foreach ($releaseKeys as $key) {
            MngMasterReleaseVersion::factory()->create([
                'id' => 'version_' . $key,
                'release_key' => $key,
                'git_revision' => 'rev_' . $key,
                'master_schema_version' => '1.0.' . ($key - 1000),
                'data_hash' => 'hash_' . $key,
            ]);

            MngMasterRelease::factory()->create([
                'id' => 'release_' . $key,
                'release_key' => $key,
                'enabled' => 1,
                'target_release_version_id' => 'version_' . $key,
                'client_compatibility_version' => '1.0.' . ($key - 1000),
                'start_at' => '2023-01-10 00:00:00',
            ]);
        }

        // Exercise
        $result = $this->repository->getApplyCollection($now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(MasterData::MASTER_RELEASE_APPLY_LIMIT, $result);

        // 最新2つのrelease_keyのみ取得されていることを確認
        $resultArray = $result->values()->toArray();
        $this->assertEquals(1005, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals(1004, $resultArray[1]['entity']->getReleaseKey());
    }

    public function test_getApplyCollection_start_atがnullも含まれる(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngMasterReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => 1001,
            'git_revision' => 'abc123',
            'master_schema_version' => '1.0.0',
            'data_hash' => 'hash001',
        ]);

        // start_atがnullのリリース情報を作成
        MngMasterRelease::factory()->create([
            'id' => 'release_001',
            'release_key' => 1001,
            'enabled' => 1,
            'target_release_version_id' => 'version_001',
            'client_compatibility_version' => '1.0.0',
            'start_at' => null,
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(1, $result);

        $resultArray = $result->values()->toArray();
        $this->assertEquals(1001, $resultArray[0]['entity']->getReleaseKey());
        $this->assertEquals('1.0.0', $resultArray[0]['client_compatibility_version']);
    }

    public function test_getApplyCollection_無効なデータは除外(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngMasterReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'master_schema_version' => '1.0.0',
                'data_hash' => 'hash001',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'master_schema_version' => '1.0.1',
                'data_hash' => 'hash002',
            ],
        ]);

        // リリース情報を作成（一部無効）
        MngMasterRelease::factory()->createMany([
            [
                'id' => 'release_001',
                'release_key' => 1001,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                'id' => 'release_002',
                'release_key' => 1002,
                'enabled' => 0, // 無効
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-10 00:00:00',
            ],
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(1, $result);

        $resultArray = $result->values()->toArray();
        $this->assertEquals(1001, $resultArray[0]['entity']->getReleaseKey());
    }

    public function test_getApplyCollection_target_release_version_idがnullの場合は除外(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngMasterReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => 1001,
            'git_revision' => 'abc123',
            'master_schema_version' => '1.0.0',
            'data_hash' => 'hash001',
        ]);

        // target_release_version_idがnullのリリース情報を作成
        MngMasterRelease::factory()->create([
            'id' => 'release_001',
            'release_key' => 1001,
            'enabled' => 1,
            'target_release_version_id' => null,
            'client_compatibility_version' => '1.0.0',
            'start_at' => '2023-01-10 00:00:00',
        ]);

        // Exercise
        $result = $this->repository->getApplyCollection($now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(0, $result);
    }

    public function test_getApplyCollection_未来の分もキャッシュされた上でキャッシュが機能する(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // リリースバージョンを作成
        MngMasterReleaseVersion::factory()->createMany([
            [
                'id' => 'version_001',
                'release_key' => 1001,
                'git_revision' => 'abc123',
                'master_schema_version' => '1.0.0',
                'data_hash' => 'hash001',
            ],
            [
                'id' => 'version_002',
                'release_key' => 1002,
                'git_revision' => 'def456',
                'master_schema_version' => '1.0.1',
                'data_hash' => 'hash002',
            ],
            [
                'id' => 'version_003',
                'release_key' => 1003,
                'git_revision' => 'ghi789',
                'master_schema_version' => '1.0.2',
                'data_hash' => 'hash003',
            ],
        ]);

        // リリース情報を作
        MngMasterRelease::factory()->createMany([
            [
                // 期間内
                'id' => 'release_001',
                'release_key' => 1001,
                'enabled' => 1,
                'target_release_version_id' => 'version_001',
                'client_compatibility_version' => '1.0.0',
                'start_at' => '2023-01-10 00:00:00',
            ],
            [
                // 未来1
                'id' => 'release_002',
                'release_key' => 1002,
                'enabled' => 1,
                'target_release_version_id' => 'version_002',
                'client_compatibility_version' => '1.0.1',
                'start_at' => '2023-01-16 00:00:00',
            ],
            [
                // 未来2
                'id' => 'release_003',
                'release_key' => 1003,
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
        $cache = $this->getFromRedis(CacheKeyUtil::getMngMasterReleaseVersionKey());
        $this->assertNull($cache);

        // Exercise - 1回目の実行
        $result1 = $this->repository->getApplyCollection($now);
        // DBから取得している
        $this->assertEquals(2, $queryCount);

        // キャッシュが作成されていることを確認
        $cache = $this->getFromRedis(CacheKeyUtil::getMngMasterReleaseVersionKey());
        $this->assertCount(3, $cache);
        $this->assertInstanceOf(MngMasterReleaseVersion::class, $cache->first());
        $this->assertEqualsCanonicalizing(
            ['version_003', 'version_002', 'version_001'],
            $cache->pluck('id')->toArray()
        );

        // Exercise - 2回目の実行（キャッシュから取得される）
        $result2 = $this->repository->getApplyCollection($now);
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
        $result3 = $this->repository->getApplyCollection($now);
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
        $result4 = $this->repository->getApplyCollection($now);
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
