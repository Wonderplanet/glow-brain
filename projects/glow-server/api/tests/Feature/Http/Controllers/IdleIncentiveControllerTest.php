<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\IdleIncentive\Models\LogIdleIncentiveReward;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\IdleIncentiveReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Carbon\CarbonImmutable;

class IdleIncentiveControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/idle_incentive/';

    public function test_receive_結合テスト()
    {
        // Setup
        // 時刻を固定
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL->value,
        ]);
        $mstStage = MstStage::factory()->create([
            'mst_quest_id' => 'normalQuest1',
            'sort_order' => 1,
        ])->toEntity();
        $rewardItemId = 'item1';
        $rankUpMaterialItemId = 'rankUpMaterial';
        MstItem::factory()->createMany([
            ['id' => $rewardItemId, 'start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59'],
            [
                'id' => $rankUpMaterialItemId,
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => UnitColorType::COLORLESS->value,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2035-12-31 23:59:59'
            ]
        ]);

        MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => 10,
            'reward_increase_interval_minutes' => 10,
            'max_idle_hours' => 100,
        ])->toEntity();
        $mstIdleIncentiveReward = MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => $mstStage->getId(),
            'base_coin_amount' => 10,
            'base_exp_amount' => 20,
        ])->toEntity();
        $mstIdleIncentiveItem = MstIdleIncentiveItem::factory()->create([
            'mst_idle_incentive_item_group_id' => $mstIdleIncentiveReward->getMstIdleIncentiveItemGroupId(),
            'mst_item_id' => $rewardItemId,
            'base_amount' => 30
        ])->toEntity();

        MstUserLevel::factory()->count(2)
            ->sequence(
                ['level' => 1, 'stamina' => 10, 'exp' => 0],
                ['level' => 2, 'stamina' => 10, 'exp' => 500],
            )->create();

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStage->getId(),
            
        ]);
        $idleStartedAt = $now->subMinutes(30);
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'idle_started_at' => $idleStartedAt->toDateTimeString(),
            'reward_mst_stage_id' => $mstStage->getId(),
        ]);

        // Exercise
        $response = $this->sendRequest('receive');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $this->assertArrayHasKey('rewards', $response->json());
        $rewards = $response->json()['rewards'];
        $this->assertCount(3, $rewards);
        $rewards = collect($rewards)
            ->map(function ($reward) {
                return $reward['reward'];
            })
            ->groupBy('resourceType');
        $this->assertEquals(30, $rewards[StringUtil::snakeToPascalCase(RewardType::COIN->value)][0]['resourceAmount']);
        $this->assertEquals(60, $rewards[StringUtil::snakeToPascalCase(RewardType::EXP->value)][0]['resourceAmount']);
        $this->assertEquals($mstIdleIncentiveItem->getMstItemId(), $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceId']);
        $this->assertEquals(90, $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceAmount']);

        $this->assertArrayHasKey('usrIdleIncentive', $response->json());
        $this->assertEquals(
            $now->copy()->toAtomString(),
            $response->json()['usrIdleIncentive']['idleStartedAt']
        );

        $this->assertArrayHasKey('usrItems', $response->json());
        $usrItems = $response->json()['usrItems'];
        $this->assertCount(1, $usrItems);
        $this->assertEquals($mstIdleIncentiveItem->getMstItemId(), $usrItems[0]['mstItemId']);

        $this->assertArrayHasKey('usrParameter', $response->json());
        $usrParamter = $response->json()['usrParameter'];
        $this->assertEquals(130, $usrParamter['coin']);
        $this->assertEquals(160, $usrParamter['exp']);

        $this->assertArrayHasKey('userLevel', $response->json());
        $userLevel = $response->json()['userLevel'];
        $this->assertEquals(100, $userLevel['beforeExp']);
        $this->assertEquals(160, $userLevel['afterExp']);
        $this->assertEquals([], $userLevel['usrLevelReward']);

        // DB確認
        $usrIdleIncentive = UsrIdleIncentive::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(
            $now->copy()->toDateTimeString(),
            $usrIdleIncentive->getIdleStartedAt()
        );

        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(130, $usrUserParameter->getCoin());
        $this->assertEquals(160, $usrUserParameter->getExp());

        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())->get()->keyBy(fn($usrItem) => $usrItem->getMstItemId());
        $this->assertCount(1, $usrItems);
        $this->assertEquals(90, $usrItems->get($mstIdleIncentiveItem->getMstItemId())->getAmount());

        // log_idle_incentive_rewards テーブル確認
        $log = LogIdleIncentiveReward::where('usr_user_id', $usrUser->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals($usrUser->id, $log->usr_user_id);
        $this->assertEquals(IdleIncentiveExecMethod::NORMAL->value, $log->exec_method);
        $this->assertEquals($idleStartedAt->toDateTimeString(), $log->idle_started_at);
        $this->assertEquals(30, $log->elapsed_minutes);
        $this->assertEquals($now->toDateTimeString(), $log->received_reward_at);

        $this->assertEqualsCanonicalizing(
            collect([
                new IdleIncentiveReward(RewardType::COIN->value, null, 30, IdleIncentiveExecMethod::NORMAL),
                new IdleIncentiveReward(RewardType::EXP->value, null, 60, IdleIncentiveExecMethod::NORMAL),
                new IdleIncentiveReward(RewardType::ITEM->value, $mstIdleIncentiveItem->getMstItemId(), 90, IdleIncentiveExecMethod::NORMAL),
            ])->map->formatToLog()->all(),
            json_decode($log->received_reward, true),
        );
    }

    /**
     * クイック探索を一次通貨使用で実行して、30分分の報酬を受け取る
     */
    public function test_quickReceiveByDiamond_結合テスト()
    {
        // Setup

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL->value,
        ]);
        $mstStage = MstStage::factory()->create([
            'mst_quest_id' => 'normalQuest1',
            'sort_order' => 1,
        ])->toEntity();
        $mstItems = MstItem::factory()->count(2)
            ->sequence(
                ['start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59'],
                ['start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59'],
            )->create()->map(fn($mstItem) => $mstItem->toEntity());
        $rewardMstItem = $mstItems->first();
        $rankUpMaterial = MstItem::factory()->create([
            'id' => 'rank_up_material',
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'effect_value' => UnitColorType::COLORLESS->value,
            'start_date' => '2000-01-01 00:00:00',
            'end_date' => '2035-12-31 23:59:59'
        ])->toEntity();

        // 30分分の報酬を付与する
        MstIdleIncentive::factory()->create([
            'reward_increase_interval_minutes' => 10,
            'quick_idle_minutes' => 30,
            'required_quick_receive_diamond_amount' => 15,
            'max_daily_diamond_quick_receive_amount' => 3,
        ])->toEntity();
        $mstIdleIncentiveReward = MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => $mstStage->getId(),
            'base_coin_amount' => 10,
            'base_exp_amount' => 20,
        ])->toEntity();
        $mstIdleIncentiveItem = MstIdleIncentiveItem::factory()->create([
            'mst_idle_incentive_item_group_id' => $mstIdleIncentiveReward->getMstIdleIncentiveItemGroupId(),
            'mst_item_id' => $rewardMstItem->getId(),
            'base_amount' => 30
        ])->toEntity();

        MstUserLevel::factory()->count(2)
            ->sequence(
                ['level' => 1, 'stamina' => 10, 'exp' => 0],
                ['level' => 2, 'stamina' => 10, 'exp' => 500],
            )->create();

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), freeDiamond: 50);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStage->getId(),
            
        ]);
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'diamond_quick_receive_count' => 0,
            'reward_mst_stage_id' => $mstStage->getId(),
        ]);

        // Exercise
        $response = $this->sendRequest('quick_receive_by_diamond');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $this->assertArrayHasKey('rewards', $response->json());
        $rewards = $response->json()['rewards'];
        $this->assertCount(3, $rewards);
        $rewards = collect($rewards)
            ->map(function ($reward) {
                return $reward['reward'];
            })
            ->groupBy('resourceType');
        $this->assertEquals(30, $rewards[StringUtil::snakeToPascalCase(RewardType::COIN->value)][0]['resourceAmount']);
        $this->assertEquals(60, $rewards[StringUtil::snakeToPascalCase(RewardType::EXP->value)][0]['resourceAmount']);
        $this->assertEquals($mstIdleIncentiveItem->getMstItemId(), $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceId']);
        $this->assertEquals(90, $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceAmount']);

        $this->assertArrayHasKey('usrIdleIncentive', $response->json());
        $this->assertEquals(
            1,
            $response->json()['usrIdleIncentive']['diamondQuickReceiveCount']
        );

        $this->assertArrayHasKey('usrItems', $response->json());
        $usrItems = collect($response->json()['usrItems'])->keyBy('mstItemId');
        $this->assertCount(1, $usrItems);
        $this->assertEquals(90, $usrItems[$rewardMstItem->getId()]['amount']);

        $this->assertArrayHasKey('usrParameter', $response->json());
        $usrParameter = $response->json()['usrParameter'];
        $this->assertEquals(130, $usrParameter['coin']);
        $this->assertEquals(160, $usrParameter['exp']);
        $this->assertEquals(50 - 15, $usrParameter['freeDiamond']);

        $this->assertArrayHasKey('userLevel', $response->json());
        $userLevel = $response->json()['userLevel'];
        $this->assertEquals(100, $userLevel['beforeExp']);
        $this->assertEquals(160, $userLevel['afterExp']);
        $this->assertEquals([], $userLevel['usrLevelReward']);

        // DB確認
        $usrIdleIncentive = UsrIdleIncentive::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(
            1,
            $usrIdleIncentive->getDiamondQuickReceiveCount(),
        );

        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(130, $usrUserParameter->getCoin());
        $this->assertEquals(160, $usrUserParameter->getExp());

        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())
            ->get()
            ->keyBy(fn($usrItem) => $usrItem->getMstItemId());
        $this->assertCount(1, $usrItems);
        $this->assertEquals(90, $usrItems->get($rewardMstItem->getId())->getAmount());
    }

    /**
     * クイック探索を広告視聴で実行して、30分分の報酬を受け取る
     */
    public function test_quickReceiveByAd_結合テスト()
    {
        // Setup

        // mst
        $mstQuest = MstQuest::factory()->create([
            'id' => 'normalQuest1',
            'quest_type' => QuestType::NORMAL->value,
        ]);
        $mstStage = MstStage::factory()->create([
            'mst_quest_id' => 'normalQuest1',
            'sort_order' => 1,
        ])->toEntity();
        $rewardItemId = 'item1';
        $rankUpMaterialItemId = 'rank_up_material';
        MstItem::factory()->createMany([
            [
                'id' => $rewardItemId,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2035-12-31 23:59:59'
            ],
            [
                'id' => $rankUpMaterialItemId,
                'type' => ItemType::RANK_UP_MATERIAL->value,
                'effect_value' => UnitColorType::COLORLESS->value,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2035-12-31 23:59:59'
            ]
        ]);

        // 30分分の報酬を付与する
        MstIdleIncentive::factory()->create([
            'reward_increase_interval_minutes' => 10,
            'quick_idle_minutes' => 30,
            'max_daily_ad_quick_receive_amount' => 3,
        ])->toEntity();
        $mstIdleIncentiveReward = MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => $mstStage->getId(),
            'base_coin_amount' => 10,
            'base_exp_amount' => 20,
        ])->toEntity();
        $mstIdleIncentiveItem = MstIdleIncentiveItem::factory()->create([
            'mst_idle_incentive_item_group_id' => $mstIdleIncentiveReward->getMstIdleIncentiveItemGroupId(),
            'mst_item_id' => $rewardItemId,
            'base_amount' => 30
        ])->toEntity();

        MstUserLevel::factory()->count(2)
            ->sequence(
                ['level' => 1, 'stamina' => 10, 'exp' => 0],
                ['level' => 2, 'stamina' => 10, 'exp' => 500],
            )->create();

        // usr
        $usrUser = $this->createUsrUser();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 100,
            'exp' => 100,
        ]);
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_stage_id' => $mstStage->getId(),
            
        ]);
        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'diamond_quick_receive_count' => 0,
            'reward_mst_stage_id' => $mstStage->getId(),
        ]);

        // Exercise
        $response = $this->sendRequest('quick_receive_by_ad');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $this->assertArrayHasKey('rewards', $response->json());
        $rewards = $response->json()['rewards'];
        $this->assertCount(3, $rewards);
        $rewards = collect($rewards)
            ->map(function ($reward) {
                return $reward['reward'];
            })
            ->groupBy('resourceType');
        $this->assertEquals(30, $rewards[StringUtil::snakeToPascalCase(RewardType::COIN->value)][0]['resourceAmount']);
        $this->assertEquals(60, $rewards[StringUtil::snakeToPascalCase(RewardType::EXP->value)][0]['resourceAmount']);
        $this->assertEquals($mstIdleIncentiveItem->getMstItemId(), $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceId']);
        $this->assertEquals(90, $rewards[StringUtil::snakeToPascalCase(RewardType::ITEM->value)][0]['resourceAmount']);

        $this->assertArrayHasKey('usrIdleIncentive', $response->json());
        $this->assertEquals(
            1,
            $response->json()['usrIdleIncentive']['adQuickReceiveCount']
        );

        $this->assertArrayHasKey('usrItems', $response->json());
        $usrItems = $response->json()['usrItems'];
        $this->assertCount(1, $usrItems);
        $this->assertEquals(90, $usrItems[0]['amount']);

        $this->assertArrayHasKey('usrParameter', $response->json());
        $usrParameter = $response->json()['usrParameter'];
        $this->assertEquals(130, $usrParameter['coin']);
        $this->assertEquals(160, $usrParameter['exp']);

        $this->assertArrayHasKey('userLevel', $response->json());
        $userLevel = $response->json()['userLevel'];
        $this->assertEquals(100, $userLevel['beforeExp']);
        $this->assertEquals(160, $userLevel['afterExp']);
        $this->assertEquals([], $userLevel['usrLevelReward']);

        // DB確認
        $usrIdleIncentive = UsrIdleIncentive::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(
            1,
            $usrIdleIncentive->getadQuickReceiveCount(),
        );

        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals(130, $usrUserParameter->getCoin());
        $this->assertEquals(160, $usrUserParameter->getExp());

        $usrItems = UsrItem::where('usr_user_id', $usrUser->getId())->get()->keyBy(fn($usrItem) => $usrItem->getMstItemId());
        $this->assertCount(1, $usrItems);
        $this->assertEquals(90, $usrItems->get($mstIdleIncentiveItem->getMstItemId())->getAmount());
    }
}
