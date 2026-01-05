<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstMissionWeekly;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Support\Traits\TestMissionTrait;

class MissionControllerTest extends BaseControllerTestCase
{
    use TestMissionTrait;

    protected string $baseUrl = '/api/mission/';

    public function test_updateAndFetch_結合テスト()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        // 時刻を固定
        $now = $this->fixTime('2024-04-14 15:00:00'); // JST: 2024-04-15 00:00:00 (月)。週の始まりは月曜日。
        $subDay = $now->subDay();

        $targetMissionTypes = [
            MissionType::DAILY,
            MissionType::WEEKLY,
            MissionType::BEGINNER,
            MissionType::ACHIEVEMENT,
        ];
        $hasBonusPointMissionTypes = [
            MissionType::DAILY,
            MissionType::WEEKLY,
            MissionType::BEGINNER,
        ];

        // mst
        foreach ($targetMissionTypes as $missionType) {
            $mstIdPrefix = $missionType->value;
            // 初心者ミッションのunlock_dayを全て3日目に設定するために、beginnerUnlockDayを指定。それ以外のミッショタイプでは使われない設定。
            $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', 1, null, 0, 3);
            $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT, null, 100, null, 0, 3);
            $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 100, null, 0, 3);
            // ボーナスポイント
            if (in_array($missionType, $hasBonusPointMissionTypes, true)) {
                $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint1', MissionCriterionType::MISSION_BONUS_POINT, null, 1);
                $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint2', MissionCriterionType::MISSION_BONUS_POINT, null, 2);
                $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint3', MissionCriterionType::MISSION_BONUS_POINT, null, 1);
            }
            $this->createMstMission($missionType, $mstIdPrefix.'lock1', MissionCriterionType::COIN_COLLECT, null, 10);
            $this->createMstMission($missionType, $mstIdPrefix.'lock2', MissionCriterionType::COIN_COLLECT, null, 20);
        }
        MstMissionDailyBonus::factory()->createMany([
            [
                'id' => 'dailyBonus-daily_bonus-1',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 1,
            ],
            [
                'id' => 'dailyBonus-daily_bonus-3',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 3,
            ],
        ]);

        // usr
        // normals
        foreach ($targetMissionTypes as $missionType) {
            $notResetAt = $now;
            $resettableAt = $this->getResettableAt($missionType, $now) ?? $now;
            $mstIdPrefix = $missionType->value;

            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 1, $subDay, null, $notResetAt);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::UNCLEAR, 1, null, null, $resettableAt);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::RECEIVED_REWARD, 1, $subDay, $subDay, $notResetAt);
            if (in_array($missionType, $hasBonusPointMissionTypes, true)) {
                $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'BonusPoint1', MissionStatus::RECEIVED_REWARD, 1, $subDay, $subDay, $notResetAt);
                $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'BonusPoint2', MissionStatus::RECEIVED_REWARD, 2, $subDay, $subDay, $notResetAt);
                $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'BonusPoint3', MissionStatus::UNCLEAR, 2, null, null, $notResetAt);
            }
            // 未開放のミッション
            // 初心者ミッションのみ、未開放でもレスポンスに含まれる
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'lock1', MissionStatus::UNCLEAR, 1, null, null, $notResetAt, MissionUnlockStatus::LOCK, 0);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'lock2', MissionStatus::CLEAR, 1, $subDay, null, $notResetAt, MissionUnlockStatus::LOCK, 0);
        }

        UsrMissionDailyBonus::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'dailyBonus-daily_bonus-1',
                'status' => MissionStatus::RECEIVED_REWARD,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => $now->toDateTimeString(),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'dailyBonus-daily_bonus-3',
                'status' => MissionStatus::UNCLEAR,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => $now->toDateTimeString(),
            ],
        ]);
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'login_day_count' => 3,
            'login_continue_day_count' => 2,
            'last_login_at' => $now->toDateTimeString(),
        ]);
        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::HAS_LOCKED->value,
            'mission_unlocked_at' => '2024-04-15 15:00:00', // 開放から2日目の設定
        ]);

        // Exercise
        $response = $this->sendRequest('update_and_fetch');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();

        // アチーブメント
        $this->assertArrayHasKey('usrMissionAchievements', $responseJson);
        $actuals = collect($responseJson['usrMissionAchievements'])->keyBy('mstMissionAchievementId');
        $this->assertCount(3, $actuals);

        $this->assertArrayHasKey('Achievement1', $actuals);
        $actual = $actuals['Achievement1'];
        $this->assertEquals('Achievement1', $actual['mstMissionAchievementId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Achievement2', $actuals);
        $actual = $actuals['Achievement2'];
        $this->assertEquals('Achievement2', $actual['mstMissionAchievementId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Achievement3', $actuals);
        $actual = $actuals['Achievement3'];
        $this->assertEquals('Achievement3', $actual['mstMissionAchievementId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(true, $actual['isReceivedReward']);

        // デイリー
        $this->assertArrayHasKey('usrMissionDailies', $responseJson);
        $actuals = collect($responseJson['usrMissionDailies'])->keyBy('mstMissionDailyId');
        $this->assertCount(3, $actuals);

        $this->assertArrayHasKey('Daily1', $actuals);
        $actual = $actuals['Daily1'];
        $this->assertEquals('Daily1', $actual['mstMissionDailyId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Daily2', $actuals);
        $actual = $actuals['Daily2'];
        $this->assertEquals('Daily2', $actual['mstMissionDailyId']);
        $this->assertEquals(0, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Daily3', $actuals);
        $actual = $actuals['Daily3'];
        $this->assertEquals('Daily3', $actual['mstMissionDailyId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(true, $actual['isReceivedReward']);

        // ウィークリー
        $this->assertArrayHasKey('usrMissionWeeklies', $responseJson);
        $actuals = collect($responseJson['usrMissionWeeklies'])->keyBy('mstMissionWeeklyId');
        $this->assertCount(3, $actuals);

        $this->assertArrayHasKey('Weekly1', $actuals);
        $actual = $actuals['Weekly1'];
        $this->assertEquals('Weekly1', $actual['mstMissionWeeklyId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Weekly2', $actuals);
        $actual = $actuals['Weekly2'];
        $this->assertEquals('Weekly2', $actual['mstMissionWeeklyId']);
        $this->assertEquals(0, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Weekly3', $actuals);
        $actual = $actuals['Weekly3'];
        $this->assertEquals('Weekly3', $actual['mstMissionWeeklyId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(true, $actual['isReceivedReward']);

        // 初心者
        // 3日目に開放される かつ 未開放 だが、初心者ミッションはレスポンスに含める仕様
        $this->assertArrayHasKey('usrMissionBeginners', $responseJson);
        $actuals = collect($responseJson['usrMissionBeginners'])->keyBy('mstMissionBeginnerId');
        $this->assertCount(5, $actuals);

        $this->assertArrayHasKey('Beginner1', $actuals);
        $actual = $actuals['Beginner1'];
        $this->assertEquals('Beginner1', $actual['mstMissionBeginnerId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Beginner2', $actuals);
        $actual = $actuals['Beginner2'];
        $this->assertEquals('Beginner2', $actual['mstMissionBeginnerId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Beginner3', $actuals);
        $actual = $actuals['Beginner3'];
        $this->assertEquals('Beginner3', $actual['mstMissionBeginnerId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(true, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Beginnerlock1', $actuals);
        $actual = $actuals['Beginnerlock1'];
        $this->assertEquals('Beginnerlock1', $actual['mstMissionBeginnerId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        $this->assertArrayHasKey('Beginnerlock2', $actuals);
        $actual = $actuals['Beginnerlock2'];
        $this->assertEquals('Beginnerlock2', $actual['mstMissionBeginnerId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        // デイリーボーナス
        $this->assertArrayHasKey('usrMissionDailyBonuses', $responseJson);
        $actuals = collect($responseJson['usrMissionDailyBonuses'])->keyBy('mstMissionDailyBonusId');
        $this->assertCount(2, $actuals);

        $this->assertArrayHasKey('dailyBonus-daily_bonus-1', $actuals);
        $actual = $actuals['dailyBonus-daily_bonus-1'];
        $this->assertEquals('dailyBonus-daily_bonus-1', $actual['mstMissionDailyBonusId']);
        $this->assertEquals(1, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(true, $actual['isReceivedReward']);

        $this->assertArrayHasKey('dailyBonus-daily_bonus-3', $actuals);
        $actual = $actuals['dailyBonus-daily_bonus-3'];
        $this->assertEquals('dailyBonus-daily_bonus-3', $actual['mstMissionDailyBonusId']);
        $this->assertEquals(2, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);

        // ボーナスポイント
        $this->assertArrayHasKey('usrMissionBonusPoints', $responseJson);
        $actuals = collect($responseJson['usrMissionBonusPoints']);
        $this->assertCount(3, $actuals);
        // daily
        $actual = $actuals->get(0);
        $this->assertEquals(MissionType::DAILY->value, $actual['missionType']);
        $this->assertEquals(2, $actual['point']);
        $responseReceivedRewardPoints = $actual['receivedRewardPoints'];
        sort($responseReceivedRewardPoints);
        $this->assertEquals([1, 2], $responseReceivedRewardPoints);
        // weekly
        $actual = $actuals->get(1);
        $this->assertEquals(MissionType::WEEKLY->value, $actual['missionType']);
        $this->assertEquals(2, $actual['point']);
        $responseReceivedRewardPoints = $actual['receivedRewardPoints'];
        sort($responseReceivedRewardPoints);
        $this->assertEquals([1, 2], $responseReceivedRewardPoints);
        // Beginner
        $actual = $actuals->get(2);
        $this->assertEquals(MissionType::BEGINNER->value, $actual['missionType']);
        $this->assertEquals(2, $actual['point']);
        $responseReceivedRewardPoints = $actual['receivedRewardPoints'];
        sort($responseReceivedRewardPoints);
        $this->assertEquals([1, 2], $responseReceivedRewardPoints);
    }

    public function test_bulkReceiveReward_デイリーミッション報酬一括受取の結合テスト()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-10-18 11:00:00');

        // mst
        // 一括受け取り対象にするミッション
        MstMissionDaily::factory()->createMany([
            [
                'id' => 'daily1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 1,
                'mst_mission_reward_group_id' => 'rewardGroup1', 'bonus_point' => 10,
            ],
            [
                'id' => 'daily2',
                'criterion_type' => MissionCriterionType::IAA_COUNT, 'criterion_value' => null, 'criterion_count' => 1,
                'mst_mission_reward_group_id' => 'rewardGroup2', 'bonus_point' => 20,
            ],
            [
                'id' => 'daily_bonusPoint_1',
                'criterion_type' => MissionCriterionType::MISSION_BONUS_POINT, 'criterion_value' => null, 'criterion_count' => 100,
                'mst_mission_reward_group_id' => '', 'bonus_point' => 0,
            ],
        ]);
        // 受取対象ではなく、別のミッションタイプのミッション
        MstMissionAchievement::factory()->createMany([
            [
                'id' => 'achievement1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 30,
                'mst_mission_reward_group_id' => 'rewardGroup1',
            ],
        ]);
        MstMissionWeekly::factory()->createMany([
            [
                'id' => 'weekly1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 20,
                'mst_mission_reward_group_id' => 'rewardGroup1',
            ],
        ]);
        MstMissionBeginner::factory()->createMany([
            [
                'id' => 'beginner1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
                'unlock_day' => 1,
                'mst_mission_reward_group_id' => 'rewardGroup1',
            ],
        ]);
        MstMissionReward::factory()->createMany([
            // rewardGroup1
            [
                'group_id' => 'rewardGroup1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 25,
            ],
            // rewardGroup2
            [
                'group_id' => 'rewardGroup2',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);

        // usr
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::DAILY->getIntValue(),
            'status' => MissionStatus::CLEAR->value, // 受取可
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'daily1', 'progress' => 10, ...$recordBase],
            ['mst_mission_id' => 'daily2', 'progress' => 20, ...$recordBase],
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 1,
        ]);
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'login_day_count' => 1,
            'login_continue_day_count' => 1,
            'last_login_at' => $now->toDateTimeString(),
        ]);
        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::HAS_LOCKED->value,
            'mission_unlocked_at' => '2023-10-01 05:00:00', // 初心者ミッションが全開放される日時で設定
        ]);

        // Exercise
        $response = $this->sendRequest(
            'bulk_receive_reward',
            [
                'missionType' => MissionType::DAILY->value,
                'mstMissionIds' => ['daily1', 'daily2'],
            ]
        );

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();
        // missionReceiveRewards: daily1,2の報酬を受け取ったことを確認
        $this->assertArrayHasKey('missionReceiveRewards', $responseJson);
        $actuals = collect($responseJson['missionReceiveRewards'])
            ->keyBy(fn($actual) => $actual['mstMissionId']);
        $this->assertCount(2, $actuals);
        $actual = $actuals->get('daily1');
        $this->assertNotNull($actual);
        $this->assertEquals(MissionType::DAILY->value, $actual['missionType']);
        $this->assertEquals(null, $actual['unreceivedRewardReason']);
        $actual = $actuals->get('daily2');
        $this->assertNotNull($actual);
        $this->assertEquals(MissionType::DAILY->value, $actual['missionType']);
        $this->assertEquals(null, $actual['unreceivedRewardReason']);
        // missionRewards: daily1,2の報酬のコイン25枚と無償プリズム100個が受け取れたことを確認
        $this->assertArrayHasKey('missionRewards', $responseJson);
        $actuals = collect($responseJson['missionRewards']);
        $this->assertCount(2, $actuals);
        $this->assertEqualsCanonicalizing(['daily1', 'daily2'], $actuals->pluck('mstMissionId')->toArray());
        $this->assertEqualsCanonicalizing([MissionType::DAILY->value, MissionType::DAILY->value], $actuals->pluck('missionType')->toArray());
        $actuals = $actuals->mapWithKeys(function ($actual) {
            $reward = $actual['reward'];
            return [$reward['resourceType'] => $reward];
        });
        $actual = $actuals->get(RewardType::COIN->value);
        $this->assertNotNull($actual);
        $this->assertEquals(25, $actual['resourceAmount']);
        $actual = $actuals->get(RewardType::FREE_DIAMOND->value);
        $this->assertNotNull($actual);
        $this->assertEquals(100, $actual['resourceAmount']);
        // usrMissionDailies: daily1,2が受け取り済ステータスになっていることを確認
        $this->assertArrayHasKey('usrMissionDailies', $responseJson);
        $actuals = collect($responseJson['usrMissionDailies'])
            ->keyBy(fn($actual) => $actual['mstMissionDailyId']);
        $this->assertCount(2, $actuals);
        $actual = $actuals->get('daily1');
        $this->assertNotNull($actual);
        $this->assertEquals(true, $actual['isReceivedReward']);
        $actual = $actuals->get('daily2');
        $this->assertNotNull($actual);
        $this->assertEquals(true, $actual['isReceivedReward']);
        // usrMissionAchievements: achievement1が未クリアだが進捗が進んでいることを確認
        $this->assertArrayHasKey('usrMissionAchievements', $responseJson);
        $actuals = collect($responseJson['usrMissionAchievements'])
            ->keyBy(fn($actual) => $actual['mstMissionAchievementId']);
        $this->assertCount(1, $actuals);
        $actual = $actuals->get('achievement1');
        $this->assertNotNull($actual);
        $this->assertEquals(25, $actual['progress']);
        $this->assertEquals(false, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);
        // usrMissionWeeklies: weekly1がクリアしていることを確認
        $this->assertArrayHasKey('usrMissionWeeklies', $responseJson);
        $actuals = collect($responseJson['usrMissionWeeklies'])
            ->keyBy(fn($actual) => $actual['mstMissionWeeklyId']);
        $this->assertCount(1, $actuals);
        $actual = $actuals->get('weekly1');
        $this->assertNotNull($actual);
        $this->assertEquals(20, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);
        // usrMissionBeginners: beginner1がクリアしていることを確認
        $this->assertArrayHasKey('usrMissionBeginners', $responseJson);
        $actuals = collect($responseJson['usrMissionBeginners'])
            ->keyBy(fn($actual) => $actual['mstMissionBeginnerId']);
        $this->assertCount(1, $actuals);
        $actual = $actuals->get('beginner1');
        $this->assertNotNull($actual);
        $this->assertEquals(10, $actual['progress']);
        $this->assertEquals(true, $actual['isCleared']);
        $this->assertEquals(false, $actual['isReceivedReward']);
        // usrMissionBonusPoints: daily1,2のボーナスポイントが受け取れたことを確認
        $this->assertArrayHasKey('usrMissionBonusPoints', $responseJson);
        $actuals = collect($responseJson['usrMissionBonusPoints'])
            ->keyBy(fn($actual) => $actual['missionType']);
        $actual = $actuals->get(MissionType::DAILY->value);
        $this->assertNotNull($actual);
        $this->assertEquals(30, $actual['point']);
        // その他: キーがあることのみ確認
        $this->assertArrayHasKey('usrParameter', $responseJson);
        $this->assertArrayHasKey('usrItems', $responseJson);
        $this->assertArrayHasKey('userLevel', $responseJson);
        $this->assertArrayHasKey('usrConditionPacks', $responseJson);
    }

    public function test_clearOnCall_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // mst
        MstMissionAchievement::factory()->createMany([
            [
                'id' => 'achievement1',
                'criterion_type' => MissionCriterionType::REVIEW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
        ]);

        MstMissionBeginner::factory()->createMany([
            // 開放、クリア
            [
                'id' => 'beginner1', 'release_key' => 1,
                'criterion_type' => MissionCriterionType::FOLLOW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
        ]);

        // Exercise
        $response = $this->sendRequest(
            'clear_on_call',
            [
                'missionType' => MissionType::ACHIEVEMENT->value,
                'mstMissionId' => 'achievement1',
            ]
        );

        $response2 = $this->sendRequest(
            'clear_on_call',
            [
                'missionType' => MissionType::BEGINNER->value,
                'mstMissionId' => 'beginner1',
            ]
        );

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response2->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();
        $responseJson2 = $response2->json();

        $this->assertArrayHasKey('usrMissionAchievements', $responseJson);
        $this->assertArrayHasKey('usrMissionBeginners', $responseJson2);
    }


    public function test_eventDailyBonus_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);
        $this->createDiamond($usrUserId, freeDiamond: 0);


        // mst
        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'missionRewardGroup1',
            ],
        ]);
        MstMissionEventDailyBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'mst_event_id' => 'event_1',
            ],
        ]);
        MstMissionReward::factory()->createMany([
            [
                'group_id' => 'missionRewardGroup1',
                'resource_type' => RewardType::EXP->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'group_id' => 'missionRewardGroup1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 200,
            ],
            [
                'group_id' => 'missionRewardGroup1',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 300,
            ],
            [
                'group_id' => 'missionRewardGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 400,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        MstUserLevelBonus::factory()->create([
            'level' => 3,
            'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
        ]);
        MstUserLevelBonusGroup::factory()->createMany([
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 100,
            ],
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item2',
                'resource_amount' => 50,
            ],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
        ]);

        // Exercise
        $response = $this->sendRequest('event_daily_bonus_update',);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();

        $this->assertArrayHasKey('eventDailyBonusRewards', $responseJson);
        $actuals = collect($responseJson['eventDailyBonusRewards']);
        $this->assertCount(4, $actuals);

        $this->assertEqualsCanonicalizing(
            ['schedule_1'],
            $actuals->pluck('mstMissionEventDailyBonusScheduleId')->unique()->toArray()
        );
        $this->assertEqualsCanonicalizing(
            [1],
            $actuals->pluck('loginDayCount')->unique()->toArray()
        );

        $actuals = $actuals
            ->map(function ($actual) {
                return $actual['reward'];
            })
            ->keyBy('resourceType');

        $actual = $actuals->get(RewardType::EXP->value);
        $this->assertEquals(100, $actual['resourceAmount']);

        $actual = $actuals->get(RewardType::COIN->value);
        $this->assertEquals(200, $actual['resourceAmount']);

        $actual = $actuals->get(RewardType::FREE_DIAMOND->value);
        $this->assertEquals(300, $actual['resourceAmount']);

        $actual = $actuals->get(RewardType::ITEM->value);
        $this->assertEquals(400, $actual['resourceAmount']);

        $this->assertArrayHasKey('usrMissionEventDailyBonusProgresses', $responseJson);
        $actuals = collect($responseJson['usrMissionEventDailyBonusProgresses']);
        $this->assertCount(1, $actuals);
        $actual = $actuals->get(0);
        $this->assertEquals('schedule_1', $actual['mstMissionEventDailyBonusScheduleId']);
        $this->assertEquals(1, $actual['progress']);

        $this->assertArrayHasKey('usrParameter', $responseJson);
        $actual = $responseJson['usrParameter'];
        $this->assertEquals(100, $actual['exp']);
        $this->assertEquals(200, $actual['coin']);
        $this->assertEquals(300, $actual['freeDiamond']);

        $this->assertArrayHasKey('usrItems', $responseJson);
        $actuals = collect($responseJson['usrItems'])->keyBy('mstItemId');
        $this->assertCount(2, $actuals);
        $this->assertEquals(500, $actuals->get('item1')['amount']);
        $this->assertEquals(50, $actuals->get('item2')['amount']);

        $this->assertArrayHasKey('usrUnits', $responseJson);
        $this->assertArrayHasKey('usrEmblems', $responseJson);

        $this->assertArrayHasKey('userLevel', $responseJson);
        $actual = $responseJson['userLevel'];
        $this->assertEquals(0, $actual['beforeExp']);
        $this->assertEquals(100, $actual['afterExp']);

        $this->assertCount(2, $actual['usrLevelReward']);
        $actuals = collect($actual['usrLevelReward'])
            ->map(function ($reward) {
                return $reward['reward'];
            })
            ->keyBy('resourceId');
        $this->assertEquals(RewardType::ITEM->value, $actuals->get('item1')['resourceType']);
        $this->assertEquals(100, $actuals->get('item1')['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $actuals->get('item2')['resourceType']);
        $this->assertEquals(50, $actuals->get('item2')['resourceAmount']);

        $this->assertArrayHasKey('usrConditionPacks', $responseJson);
    }

    public function test_bulkReceiveReward_期間限定ミッション報酬一括受取の結合テスト()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-10-18 11:00:00');

        // mst
        // 一括受け取り対象にするミッション
        MstMissionLimitedTerm::factory()->createMany([
            [
                'id' => 'term1',
                'progress_group_key' => 'group1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT, 'criterion_value' => null, 'criterion_count' => 1,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'mst_mission_reward_group_id' => 'rewardGroup1',
            ],
            [
                'id' => 'term2',
                'progress_group_key' => 'group2',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_SCORE, 'criterion_value' => null, 'criterion_count' => 1000,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
                'mst_mission_reward_group_id' => 'rewardGroup2',
            ]
        ]);
        MstMissionReward::factory()->createMany([
            // rewardGroup1
            [
                'group_id' => 'rewardGroup1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 25,
            ],
            // rewardGroup2
            [
                'group_id' => 'rewardGroup2',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);

        // usr
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'status' => MissionStatus::CLEAR->value, // 受取可
            'is_open' => MissionUnlockStatus::OPEN->value,
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ];
        UsrMissionLimitedTerm::factory()->createMany([
            ['mst_mission_limited_term_id' => 'term1', ...$recordBase],
            ['mst_mission_limited_term_id' => 'term2', ...$recordBase],
        ]);
        $this->createDiamond($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 1,
        ]);
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'login_day_count' => 1,
            'login_continue_day_count' => 1,
            'last_login_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $response = $this->sendRequest(
            'bulk_receive_reward',
            [
                'missionType' => MissionType::LIMITED_TERM->value,
                'mstMissionIds' => ['term1', 'term2'],
            ]
        );

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();
        // missionReceiveRewards: term1,2の報酬を受け取ったことを確認
        $this->assertArrayHasKey('missionReceiveRewards', $responseJson);
        $actuals = collect($responseJson['missionReceiveRewards'])
            ->keyBy(fn($actual) => $actual['mstMissionId']);
        $this->assertCount(2, $actuals);
        $actual = $actuals->get('term1');
        $this->assertNotNull($actual);
        $this->assertEquals(MissionType::LIMITED_TERM->value, $actual['missionType']);
        $this->assertEquals(null, $actual['unreceivedRewardReason']);
        $actual = $actuals->get('term2');
        $this->assertNotNull($actual);
        $this->assertEquals(MissionType::LIMITED_TERM->value, $actual['missionType']);
        $this->assertEquals(null, $actual['unreceivedRewardReason']);
        // missionRewards: term1,2の報酬のコイン25枚と無償プリズム100個が受け取れたことを確認
        $this->assertArrayHasKey('missionRewards', $responseJson);
        $actuals = collect($responseJson['missionRewards']);
        $this->assertCount(2, $actuals);
        $this->assertEqualsCanonicalizing(['term1', 'term2'], $actuals->pluck('mstMissionId')->toArray());
        $this->assertEqualsCanonicalizing([MissionType::LIMITED_TERM->value, MissionType::LIMITED_TERM->value], $actuals->pluck('missionType')->toArray());
        $actuals = $actuals->mapWithKeys(function ($actual) {
            $reward = $actual['reward'];
            return [$reward['resourceType'] => $reward];
        });
        $actual = $actuals->get(RewardType::COIN->value);
        $this->assertNotNull($actual);
        $this->assertEquals(25, $actual['resourceAmount']);
        $actual = $actuals->get(RewardType::FREE_DIAMOND->value);
        $this->assertNotNull($actual);
        $this->assertEquals(100, $actual['resourceAmount']);
        // usrMissionLimitedTerms: term1,2が受け取り済ステータスになっていることを確認
        $this->assertArrayHasKey('usrMissionLimitedTerms', $responseJson);
        $actuals = collect($responseJson['usrMissionLimitedTerms'])
            ->keyBy(fn($actual) => $actual['mstMissionLimitedTermId']);
        $this->assertCount(2, $actuals);
        $actual = $actuals->get('term1');
        $this->assertNotNull($actual);
        $this->assertEquals(true, $actual['isReceivedReward']);
        $actual = $actuals->get('term2');
        $this->assertNotNull($actual);
        $this->assertEquals(true, $actual['isReceivedReward']);
        // その他: キーがあることのみ確認
        $this->assertArrayHasKey('usrParameter', $responseJson);
        $this->assertArrayHasKey('usrItems', $responseJson);
        $this->assertArrayHasKey('userLevel', $responseJson);
        $this->assertArrayHasKey('usrConditionPacks', $responseJson);
    }
}
