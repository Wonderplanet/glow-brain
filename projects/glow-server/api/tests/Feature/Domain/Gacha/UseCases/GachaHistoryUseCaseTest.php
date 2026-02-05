<?php

namespace Tests\Feature\Domain\Gacha\UseCases;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\UseCases\GachaHistoryUseCase;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Entities\Rewards\StepUpGachaStepReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprStepUpGacha;
use App\Domain\Resource\Mst\Models\OprStepUpGachaStep;
use Carbon\CarbonImmutable;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class GachaHistoryUseCaseTest extends TestCase
{
    private GachaHistoryUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(GachaHistoryUseCase::class);
    }

    public function testExec_通常ガシャの履歴取得()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        OprGacha::factory()->create([
            'id' => 'normal_gacha_1',
            'gacha_type' => GachaType::NORMAL->value,
        ]);

        // 通常ガシャの履歴を作成（stepNumber, loopCountはnull）
        $histories = collect([
            new GachaHistory(
                'normal_gacha_1',
                'Diamond',
                null,
                300,
                10,
                $now->subHours(1),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'normal_gacha_1', 1),
                ]),
                null, // stepNumber
                null, // loopCount
                null  // stepRewards
            ),
        ]);

        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $histories);

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(1, $resultData->gachaHistories);
        
        $history = $resultData->gachaHistories->first();
        $this->assertEquals('normal_gacha_1', $history->getOprGachaId());
        $this->assertNull($history->getStepNumber());
        $this->assertNull($history->getLoopCount());
    }

    public function testExec_ステップアップガシャの履歴取得()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        OprGacha::factory()->create([
            'id' => 'stepup_gacha_1',
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => 'stepup_gacha_1',
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        // ステップアップガシャの履歴を作成（stepNumber, loopCountあり）
        $histories = collect([
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                300,
                10,
                $now->subHours(3),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'stepup_gacha_1', 1),
                ]),
                1, // stepNumber
                0, // loopCount
                null // stepRewards
            ),
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                600,
                10,
                $now->subHours(2),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_2', 1, 'stepup_gacha_1', 2),
                ]),
                2, // stepNumber
                0, // loopCount
                null // stepRewards
            ),
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                900,
                10,
                $now->subHours(1),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'stepup_gacha_1', 3),
                ]),
                3, // stepNumber
                0, // loopCount
                null // stepRewards
            ),
        ]);

        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $histories);

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(3, $resultData->gachaHistories);
        
        // 履歴が新しい順にソートされていることを確認
        $history1 = $resultData->gachaHistories[0];
        $this->assertEquals(3, $history1->getStepNumber());
        $this->assertEquals(0, $history1->getLoopCount());

        $history2 = $resultData->gachaHistories[1];
        $this->assertEquals(2, $history2->getStepNumber());
        $this->assertEquals(0, $history2->getLoopCount());

        $history3 = $resultData->gachaHistories[2];
        $this->assertEquals(1, $history3->getStepNumber());
        $this->assertEquals(0, $history3->getLoopCount());
    }

    public function testExec_周回時のステップアップガシャ履歴()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        OprGacha::factory()->create([
            'id' => 'stepup_gacha_1',
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => 'stepup_gacha_1',
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        // 周回を含む履歴を作成
        $histories = collect([
            // 1周目のステップ5
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                1500,
                10,
                $now->subHours(2),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'stepup_gacha_1', 1),
                ]),
                5, // stepNumber
                0, // loopCount
                null // stepRewards
            ),
            // 2周目のステップ1
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                300,
                10,
                $now->subHours(1),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_2', 1, 'stepup_gacha_1', 1),
                ]),
                1, // stepNumber
                1, // loopCount
                null // stepRewards
            ),
        ]);

        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $histories);

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(2, $resultData->gachaHistories);
        
        // 2周目のステップ1
        $history1 = $resultData->gachaHistories[0];
        $this->assertEquals(1, $history1->getStepNumber());
        $this->assertEquals(1, $history1->getLoopCount());

        // 1周目のステップ5
        $history2 = $resultData->gachaHistories[1];
        $this->assertEquals(5, $history2->getStepNumber());
        $this->assertEquals(0, $history2->getLoopCount());
    }

    public function testExec_複数ガシャの混在履歴()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        OprGacha::factory()->createMany([
            ['id' => 'normal_gacha_1', 'gacha_type' => GachaType::NORMAL->value],
            ['id' => 'stepup_gacha_1', 'gacha_type' => GachaType::STEPUP->value],
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => 'stepup_gacha_1',
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        // 通常ガシャとステップアップガシャの履歴を混在させる
        $histories = collect([
            new GachaHistory(
                'normal_gacha_1',
                'Diamond',
                null,
                100,
                1,
                $now->subHours(3),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'normal_gacha_1', 1),
                ]),
                null,
                null,
                null
            ),
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                300,
                10,
                $now->subHours(2),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_2', 1, 'stepup_gacha_1', 1),
                ]),
                1,
                0,
                null
            ),
            new GachaHistory(
                'normal_gacha_1',
                'Diamond',
                null,
                1000,
                10,
                $now->subHours(1),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'normal_gacha_1', 1),
                ]),
                null,
                null,
                null
            ),
        ]);

        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $histories);

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(3, $resultData->gachaHistories);
        
        // 通常ガシャ（最新）
        $history1 = $resultData->gachaHistories[0];
        $this->assertEquals('normal_gacha_1', $history1->getOprGachaId());
        $this->assertNull($history1->getStepNumber());
        $this->assertNull($history1->getLoopCount());

        // ステップアップガシャ
        $history2 = $resultData->gachaHistories[1];
        $this->assertEquals('stepup_gacha_1', $history2->getOprGachaId());
        $this->assertEquals(1, $history2->getStepNumber());
        $this->assertEquals(0, $history2->getLoopCount());

        // 通常ガシャ（古い）
        $history3 = $resultData->gachaHistories[2];
        $this->assertEquals('normal_gacha_1', $history3->getOprGachaId());
        $this->assertNull($history3->getStepNumber());
        $this->assertNull($history3->getLoopCount());
    }

    public function testExec_履歴なし()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        // 履歴を設定しない

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(0, $resultData->gachaHistories);
    }

    public function testExec_ステップアップガシャのおまけ報酬付き履歴()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        $usrUserId = $usrUser->getId();

        OprGacha::factory()->create([
            'id' => 'stepup_gacha_1',
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => 'stepup_gacha_1',
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        // おまけ報酬を含むステップアップガシャの履歴を作成
        $histories = collect([
            new GachaHistory(
                'stepup_gacha_1',
                'Diamond',
                null,
                300,
                10,
                $now->subHours(1),
                collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'stepup_gacha_1', 1),
                ]),
                1, // stepNumber
                0, // loopCount
                collect([
                    new StepUpGachaStepReward(
                        RewardType::ITEM->value,
                        'item_1',
                        100,
                        'stepup_gacha_1',
                        1,
                        0
                    ),
                    new StepUpGachaStepReward(
                        RewardType::ITEM->value,
                        'item_2',
                        50,
                        'stepup_gacha_1',
                        1,
                        0
                    ),
                ])
            ),
        ]);

        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $histories);

        // Exercise
        $resultData = $this->useCase->exec(new CurrentUser($usrUserId), $now);

        // Verify
        $this->assertCount(1, $resultData->gachaHistories);
        
        $history = $resultData->gachaHistories->first();
        $this->assertEquals('stepup_gacha_1', $history->getOprGachaId());
        $this->assertEquals(1, $history->getStepNumber());
        $this->assertEquals(0, $history->getLoopCount());
        
        // formatToResponse()を呼び出してstepRewardsが含まれることを確認
        $response = $history->formatToResponse();
        $this->assertNotNull($response['stepRewards']);
        $this->assertCount(2, $response['stepRewards']);
        $this->assertEquals('item_1', $response['stepRewards'][0]['resourceId']);
        $this->assertEquals(100, $response['stepRewards'][0]['resourceAmount']);
        $this->assertEquals('item_2', $response['stepRewards'][1]['resourceId']);
        $this->assertEquals(50, $response['stepRewards'][1]['resourceAmount']);
    }

    public function test_GachaHistory_旧データの後方互換性()
    {
        // 旧形式のシリアライズデータを模擬（stepNumber, loopCount, stepRewardsが存在しない）
        $oldSerializedData = [
            'oprGachaId' => 'old_gacha_1',
            'costType' => 'Diamond',
            'costId' => null,
            'costNum' => 300,
            'drawCount' => 10,
            'playedAt' => CarbonImmutable::now(),
            'results' => collect([
                new GachaReward(
                    RewardType::ITEM->value,
                    'item_1',
                    1,
                    'old_gacha_1',
                    0
                )
            ]),
            // stepNumber, loopCount, stepRewards は存在しない（旧データ）
        ];

        // __unserialize()を使用して復元
        $history = new GachaHistory(
            'dummy',
            'dummy',
            null,
            0,
            0,
            CarbonImmutable::now(),
            collect()
        );
        $history->__unserialize($oldSerializedData);

        // 検証: デフォルト値がnullとして設定される
        $this->assertEquals('old_gacha_1', $history->getOprGachaId());
        $this->assertNull($history->getStepNumber());
        $this->assertNull($history->getLoopCount());
        $this->assertFalse($history->hasStepUpInfo());

        // formatToResponse()が正常に動作する
        $response = $history->formatToResponse();
        $this->assertEquals('old_gacha_1', $response['oprGachaId']);
        $this->assertNull($response['stepUpInfo']);
        $this->assertNull($response['stepRewards']);
    }

    public function test_GachaHistory_新データのserialize_unserialize()
    {
        // 新形式データ（ステップアップ情報付き）
        $stepRewards = collect([
            new StepUpGachaStepReward(
                RewardType::ITEM->value,
                'item_1',
                100,
                'stepup_gacha_1',
                1,
                0
            ),
        ]);

        $original = new GachaHistory(
            'stepup_gacha_1',
            'Diamond',
            null,
            300,
            10,
            CarbonImmutable::parse('2026-01-15 12:00:00'),
            collect([
                new GachaReward(
                    RewardType::ITEM->value,
                    'item_1',
                    1,
                    'stepup_gacha_1',
                    0
                )
            ]),
            1,      // stepNumber
            0,      // loopCount
            $stepRewards
        );

        // serialize → unserialize
        $serialized = $original->__serialize();
        
        $restored = new GachaHistory(
            'dummy',
            'dummy',
            null,
            0,
            0,
            CarbonImmutable::now(),
            collect()
        );
        $restored->__unserialize($serialized);

        // 検証: 全データが正しく復元される
        $this->assertEquals('stepup_gacha_1', $restored->getOprGachaId());
        $this->assertEquals(1, $restored->getStepNumber());
        $this->assertEquals(0, $restored->getLoopCount());
        $this->assertTrue($restored->hasStepUpInfo());

        $response = $restored->formatToResponse();
        $this->assertNotNull($response['stepUpInfo']);
        $this->assertEquals(1, $response['stepUpInfo']['stepNumber']);
        $this->assertEquals(0, $response['stepUpInfo']['loopCount']);
        $this->assertNotNull($response['stepRewards']);
        $this->assertCount(1, $response['stepRewards']);
    }

    public function test_GachaHistory_新旧データ混在のCollection()
    {
        // 旧データ（ステップアップ情報なし）
        $oldData = [
            'oprGachaId' => 'old_gacha_1',
            'costType' => 'Diamond',
            'costId' => null,
            'costNum' => 300,
            'drawCount' => 10,
            'playedAt' => CarbonImmutable::parse('2026-01-14 12:00:00'),
            'results' => collect([
                new GachaReward(
                    RewardType::ITEM->value,
                    'item_1',
                    1,
                    'old_gacha_1',
                    0
                )
            ]),
        ];

        $oldHistory = new GachaHistory(
            'dummy',
            'dummy',
            null,
            0,
            0,
            CarbonImmutable::now(),
            collect()
        );
        $oldHistory->__unserialize($oldData);

        // 新データ（ステップアップ情報あり）
        $newHistory = new GachaHistory(
            'stepup_gacha_1',
            'Diamond',
            null,
            300,
            10,
            CarbonImmutable::parse('2026-01-15 12:00:00'),
            collect([
                new GachaReward(
                    RewardType::ITEM->value,
                    'item_2',
                    1,
                    'stepup_gacha_1',
                    0
                )
            ]),
            1,
            0,
            null
        );

        // 混在Collection
        $mixedHistories = collect([$oldHistory, $newHistory]);

        // 検証: 両方のデータがformatToResponse()で正常に変換できる
        $responses = $mixedHistories->map(fn($h) => $h->formatToResponse());
        
        $this->assertCount(2, $responses);
        
        // 旧データの検証
        $this->assertEquals('old_gacha_1', $responses[0]['oprGachaId']);
        $this->assertNull($responses[0]['stepUpInfo']);
        $this->assertNull($responses[0]['stepRewards']);
        
        // 新データの検証
        $this->assertEquals('stepup_gacha_1', $responses[1]['oprGachaId']);
        $this->assertNotNull($responses[1]['stepUpInfo']);
        $this->assertEquals(1, $responses[1]['stepUpInfo']['stepNumber']);
        $this->assertEquals(0, $responses[1]['stepUpInfo']['loopCount']);
    }
}
