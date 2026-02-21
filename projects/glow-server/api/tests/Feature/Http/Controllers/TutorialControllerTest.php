<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\DailyBonus\Enums\DailyBonusType;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\GachaUnlockConditionType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;

class TutorialControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/tutorial/';

    public function test_導入パートからメインパート完了まで進行できる()
    {
        // Setup
        $this->fixTime('2025-02-13 00:00:00');
        $usrUser = $this->createUsrUser([
            'tutorial_status' => '',
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 100,
        ]);
        $this->createDiamond($usrUserId);

        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit_1',
            'level' => 1,
        ]);

        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::INTRO,
                'sort_order' => 1,
                'function_name' => 'SetUserName',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => 'StartMainPart1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => 'UnitLevelUp',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                // 旧チュートリアルの完了の1つ前
                'type' => TutorialType::MAIN,
                'sort_order' => 5,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 6,
                'function_name' => TutorialFunctionName::MAIN_PART_COMPLETED,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
        ]);
        OprGacha::factory()->createMany([
            [
                'id' => 'tutorial_gacha_1',
                'gacha_type' => GachaType::TUTORIAL,
                'upper_group' => 'None',
                'enable_ad_play' => 0,
                'enable_add_ad_play_upper' => 0,
                'ad_play_interval_time' => null,
                'multi_draw_count' => 10,
                'multi_fixed_prize_count' => 1,
                'daily_play_limit_count' => null,
                'total_play_limit_count' => null,
                'daily_ad_limit_count' => null,
                'total_ad_limit_count' => null,
                'prize_group_id' => 'prize_group_1',
                'fixed_prize_group_id' => 'fixed_prize_group_1',
                'appearance_condition' => 'Always',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'id' => 'unlock_gacha_after_main_part_completed',
                'unlock_condition_type' => GachaUnlockConditionType::MAIN_PART_TUTORIAL_COMPLETE->value,
                'unlock_duration_hours' => 10
            ],

        ]);
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => 'tutorial_gacha_1',
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => null,
            'cost_num' => 0,
            'draw_count' => 10,
        ]);
        OprGachaPrize::factory()->createMany([
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 1,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 0,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_3',
                'resource_amount' => 1,
                'weight' => 98,
                'pickup' => 0,
            ],
            [
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
            [
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
        ]);
        MstUnit::factory()->createMany([
            ['id' => 'unit_1', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_1'],
            ['id' => 'unit_2', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_2'],
            ['id' => 'unit_3', 'unit_label' => UnitLabel::DROP_SR, 'fragment_mst_item_id' => 'fragment_unit_3'],
        ]);
        MstUnitFragmentConvert::factory()->createMany([
            ['unit_label' => UnitLabel::DROP_SR, 'convert_amount' => 10],
            ['unit_label' => UnitLabel::DROP_UR, 'convert_amount' => 20],
        ]);
        MstUnitLevelUp::factory()->createMany([
            [
                'unit_label' => UnitLabel::DROP_UR,
                'level' => 2,
                'required_coin' => 50,
            ],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'fragment_unit_1'],
            ['id' => 'fragment_unit_2'],
            ['id' => 'fragment_unit_3'],
            ['id' => 'item_coin'],
            ['id' => 'item_diamond'],
        ]);

        // ログボのマスターデータ設定
        $rewardGroupId1 = 'reward_group_daily_bonus_1';
        $rewardGroupId2 = 'reward_group_daily_bonus_2';

        MstMissionDailyBonus::factory()->createMany([
            [
                'id' => 'daily_bonus_1',
                'mission_daily_bonus_type' => DailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => $rewardGroupId1,
                'sort_order' => 1,
            ],
            [
                'id' => 'daily_bonus_2',
                'mission_daily_bonus_type' => DailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => $rewardGroupId2,
                'sort_order' => 2,
            ],
        ]);

        // ログボ報酬のマスターデータ設定
        MstMissionReward::factory()->createMany([
            [
                'id' => 'reward_daily_bonus_1',
                'group_id' => $rewardGroupId1,
                'resource_type' => RewardType::COIN,
                'resource_id' => null,
                'resource_amount' => 1000,
                'sort_order' => 1,
            ],
            [
                'id' => 'reward_daily_bonus_2',
                'group_id' => $rewardGroupId2,
                'resource_type' => RewardType::FREE_DIAMOND,
                'resource_id' => null,
                'resource_amount' => 100,
                'sort_order' => 1,
            ],
        ]);

        MstMissionEventDailyBonusSchedule::factory()->createMany([
            [
                'id' => 'event_daily_bonus_schedule_1',
                'mst_event_id' => 'event1',
                'start_at' => '2025-02-13 00:00:00',
                'end_at' => '2025-02-20 23:59:59',
                'release_key' => 1,
            ],
        ]);
        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'event_daily_bonus_1',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => $rewardGroupId1,
                'sort_order' => 1,
                'mst_mission_event_daily_bonus_schedule_id' => 'event_daily_bonus_schedule_1',
            ],
        ]);
        MstEvent::factory()->create([
            'id' => 'event1',
            'start_at' => '2025-01-01 00:00:00',
            'end_at' => '2025-03-30 23:59:59',
        ]);

        // Exercise & Verify

        // 順番通りじゃないので失敗
        $response = $this->sendRequest('update_status', ['mstTutorialFunctionName' => 'StartMainPart1']);
        $response->assertStatus(HttpStatusCode::ERROR);

        // 導入パート
        $response = $this->sendRequest('update_status', ['mstTutorialFunctionName' => 'SetUserName']);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $usrUser->refresh();
        $this->assertEquals('SetUserName', $usrUser->getTutorialStatus());

        // メインパート
        $response = $this->sendRequest('update_status', ['mstTutorialFunctionName' => 'StartMainPart1']);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        // レスポンス確認 - メインパート完了前でチュートリアル進行中
        $response = $response->json();

        $this->assertArrayHasKey('usrIdleIncentive', $response);
        $this->assertNull($response['usrIdleIncentive']);

        $this->assertArrayHasKey('usrGachas', $response);
        $this->assertCount(0, $response['usrGachas']);

        $this->assertArrayHasKey('dailyBonusRewards', $response);
        $this->assertCount(0, $response['dailyBonusRewards']);

        $this->assertArrayHasKey('eventDailyBonusRewards', $response);
        $this->assertCount(0, $response['eventDailyBonusRewards']);

        $this->assertArrayHasKey('usrMissionEventDailyBonusProgresses', $response);
        $this->assertCount(0, $response['usrMissionEventDailyBonusProgresses']);

        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertArrayHasKey('usrUnits', $response);
        $this->assertArrayHasKey('usrItems', $response);
        $this->assertArrayHasKey('usrEmblems', $response);
        $this->assertArrayHasKey('usrConditionPacks', $response);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals('StartMainPart1', $usrUser->getTutorialStatus());

        // 順番通りじゃないので失敗
        $response = $this->sendRequest('update_status', ['mstTutorialFunctionName' => TutorialFunctionName::MAIN_PART_COMPLETED,]);
        $response->assertStatus(HttpStatusCode::ERROR);

        // gacha_draw前にはgacha_confirmは失敗する
        $response = $this->sendRequest('gacha_confirm', []);
        $response->assertStatus(HttpStatusCode::ERROR);

        // gacha_draw 1回目
        $response = $this->sendRequest('gacha_draw', []);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $usrUser->refresh();
        $this->assertEquals('StartMainPart1', $usrUser->getTutorialStatus());

        // gacha_draw 2回目 引き直しガシャなので何度も実行可
        $response = $this->sendRequest('gacha_draw', []);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $usrUser->refresh();
        $this->assertEquals('StartMainPart1', $usrUser->getTutorialStatus());

        $response = $this->sendRequest('gacha_confirm', []);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $usrUser->refresh();
        $this->assertEquals(TutorialFunctionName::GACHA_CONFIRMED->value, $usrUser->getTutorialStatus());
        // リソース配布されていることを簡易的に確認
        $this->assertTrue(UsrUnit::where('usr_user_id', $usrUserId)->exists());

        // ユニットレベルアップ
        $response = $this->sendRequest('unit_level_up', [
            'mstTutorialFunctionName' => 'UnitLevelUp',
            'usrUnitId' => $usrUnit->getId(),
            'level' => 2,
        ]);
        // レスポンス確認
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response = $response->json();
        $this->assertEquals(2, $response['usrUnit']['level']);
        $this->assertEquals(50, $response['usrParameter']['coin']);
        $this->assertEquals('UnitLevelUp', $response['tutorialStatus']);
        // DB確認
        $usrUnit->refresh();
        $this->assertEquals(2, $usrUnit->getLevel());
        $usrUserParameter = UsrUserParameter::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(50, $usrUserParameter->getCoin());
        $usrUser->refresh();
        $this->assertEquals('UnitLevelUp', $usrUser->getTutorialStatus());

        // メインパート完了
        $this->fixTime('2025-02-13 01:00:00');
        $response = $this->sendRequest('update_status', ['mstTutorialFunctionName' => TutorialFunctionName::MAIN_PART_COMPLETED,]);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        // レスポンス確認 - メインパート完了時
        $response = $response->json();

        $this->assertArrayHasKey('usrIdleIncentive', $response);
        $this->assertNotNull($response['usrIdleIncentive']);
        $this->assertIsArray($response['usrIdleIncentive']);
        $this->assertEquals(StringUtil::convertToISO8601('2025-02-13 01:00:00'), $response['usrIdleIncentive']['idleStartedAt']);

        $this->assertArrayHasKey('usrGachas', $response);
        $this->assertGreaterThan(0, count($response['usrGachas']));
        $gachasByOprId = collect($response['usrGachas'])->keyBy('oprGachaId');
        $this->assertArrayHasKey('unlock_gacha_after_main_part_completed', $gachasByOprId);

        $this->assertArrayHasKey('dailyBonusRewards', $response);
        $this->assertCount(1, $response['dailyBonusRewards']);
        $actual = collect($response['dailyBonusRewards'])->keyBy('loginDayCount')->get(1);
        $this->assertEquals(DailyBonusType::DAILY_BONUS->value, $actual['missionType']);
        $this->assertEquals(1, $actual['loginDayCount']);

        $this->assertArrayHasKey('eventDailyBonusRewards', $response);
        $this->assertCount(1, $response['eventDailyBonusRewards']);
        $actual = collect($response['eventDailyBonusRewards'])->keyBy('loginDayCount')->get(1);
        $this->assertEquals('event_daily_bonus_schedule_1', $actual['mstMissionEventDailyBonusScheduleId']);
        $this->assertEquals(1, $actual['loginDayCount']);

        $this->assertArrayHasKey('usrMissionEventDailyBonusProgresses', $response);
        $this->assertCount(1, $response['usrMissionEventDailyBonusProgresses']);
        $this->assertEquals('event_daily_bonus_schedule_1', $response['usrMissionEventDailyBonusProgresses'][0]['mstMissionEventDailyBonusScheduleId']);
        $this->assertEquals(1, $response['usrMissionEventDailyBonusProgresses'][0]['progress']);

        $this->assertArrayHasKey('usrParameter', $response);
        $this->assertEquals(100 - 50 + 1000 + 1000, $response['usrParameter']['coin']); // 初期値100 - ユニットレベルアップ消費50 + ログボ1000 + イベントログボ1000

        $this->assertArrayHasKey('usrUnits', $response);
        $this->assertArrayHasKey('usrItems', $response);
        $this->assertArrayHasKey('usrEmblems', $response);
        $this->assertArrayHasKey('usrConditionPacks', $response);

        // DB確認
        $usrUser->refresh();
        $this->assertEquals(TutorialFunctionName::MAIN_PART_COMPLETED->value, $usrUser->getTutorialStatus());
    }
}
