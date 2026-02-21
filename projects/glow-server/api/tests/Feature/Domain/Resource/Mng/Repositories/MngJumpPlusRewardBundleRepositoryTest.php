<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngJumpPlusReward;
use App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule;
use App\Domain\Resource\Mng\Repositories\MngJumpPlusRewardBundleRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MngJumpPlusRewardBundleRepositoryTest extends TestCase
{
    private MngJumpPlusRewardBundleRepository $mngJumpPlusRewardBundleRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngJumpPlusRewardBundleRepository = app()->make(MngJumpPlusRewardBundleRepository::class);
    }

    /**
     * テスト用のジャンプ+連携報酬データを作成
     * 期間内2つ、期限切れ1つ、未来1つのデータを作成
     */
    private function createJumpPlusRewardMasterData(): void
    {
        // スケジュールデータを作成
        MngJumpPlusRewardSchedule::factory()->createMany([
            // 期間内1
            [
                'id' => 'schedule_001',
                'group_id' => 'group_001',
                'start_at' => '2023-01-10 00:00:00',
                'end_at' => '2023-01-20 23:59:59',
            ],
            // 期間内2
            [
                'id' => 'schedule_002',
                'group_id' => 'group_002',
                'start_at' => '2023-01-12 00:00:00',
                'end_at' => '2023-01-18 23:59:59',
            ],
            // 期限切れ
            [
                'id' => 'schedule_003',
                'group_id' => 'group_003',
                'start_at' => '2023-01-01 00:00:00',
                'end_at' => '2023-01-08 23:59:59',
            ],
            // 未来
            [
                'id' => 'schedule_004',
                'group_id' => 'group_004',
                'start_at' => '2023-01-20 00:00:00',
                'end_at' => '2023-01-25 23:59:59',
            ],
        ]);

        // 報酬データを作成
        MngJumpPlusReward::factory()->createMany([
            [
                'id' => 'reward_001_1',
                'group_id' => 'group_001',
                'resource_type' => RewardType::ITEM,
                'resource_id' => 'item_001',
                'resource_amount' => 100,
            ],
            [
                'id' => 'reward_001_2',
                'group_id' => 'group_001',
                'resource_type' => RewardType::COIN,
                'resource_id' => null,
                'resource_amount' => 1000,
            ],
            [
                'id' => 'reward_002_1',
                'group_id' => 'group_002',
                'resource_type' => RewardType::ITEM,
                'resource_id' => 'item_002',
                'resource_amount' => 50,
            ],
            [
                'id' => 'reward_003_1',
                'group_id' => 'group_003',
                'resource_type' => RewardType::ITEM,
                'resource_id' => 'item_003',
                'resource_amount' => 200,
            ],
            [
                'id' => 'reward_004_1',
                'group_id' => 'group_004',
                'resource_type' => RewardType::ITEM,
                'resource_id' => 'item_004',
                'resource_amount' => 300,
            ],
        ]);
    }

    public function test_getActiveMngJumpPlusRewardBundles_キャッシュ動作確認_取得時間に応じて有効データが変わる(): void
    {
        // Setup
        $this->createJumpPlusRewardMasterData();
        $cacheKey = CacheKeyUtil::getMngJumpPlusRewardBundleKey();

        // キャッシュが空であることを確認
        $this->assertNull($this->getFromRedis($cacheKey));

        // sql発行回数
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // Exercise 1 - 初回実行でキャッシュ作成
        $now = $this->fixTime('2023-01-15 12:00:00');
        $result1 = $this->mngJumpPlusRewardBundleRepository->getActiveMngJumpPlusRewardBundles($now);
        $this->assertEqualsCanonicalizing(['schedule_001', 'schedule_002'], $result1->keys()->toArray());
        // SQL発行回数が2回であることを確認（MngJumpPlusRewardSchedule + MngJumpPlusReward）
        $this->assertEquals(2, $queryCount);
        // キャッシュが作成されていることを確認
        $this->assertNotNull($this->getFromRedis($cacheKey));

        // Exercise 2 - 2回目実行でキャッシュから取得
        $now = $this->fixTime('2023-01-20 12:00:00');
        $result2 = $this->mngJumpPlusRewardBundleRepository->getActiveMngJumpPlusRewardBundles($now);
        $this->assertEquals(2, $queryCount); // SQL発行回数が変わらないことを確認
        $this->assertEqualsCanonicalizing(['schedule_001', 'schedule_004'], $result2->keys()->toArray());

        // Exercise 3 - 3回目実行でキャッシュから取得
        $now = $this->fixTime('2023-01-23 12:00:00');
        $result3 = $this->mngJumpPlusRewardBundleRepository->getActiveMngJumpPlusRewardBundles($now);
        $this->assertEquals(2, $queryCount); // SQL発行回数が変わらないことを確認
        $this->assertEqualsCanonicalizing(['schedule_004'], $result3->keys()->toArray());
    }

    public function test_deleteAllCache(): void
    {
        // Setup
        $this->createJumpPlusRewardMasterData();
        $now = $this->fixTime('2023-01-15 12:00:00');

        // キャッシュを作成
        $this->mngJumpPlusRewardBundleRepository->getActiveMngJumpPlusRewardBundles($now);
        $cacheKey = CacheKeyUtil::getMngJumpPlusRewardBundleKey();
        $this->assertNotNull($this->getFromRedis($cacheKey));

        // Execute: キャッシュ削除
        $this->mngJumpPlusRewardBundleRepository->deleteAllCache();

        // Verify: キャッシュが削除されていることを確認
        $this->assertNull($this->getFromRedis($cacheKey));
    }
}
