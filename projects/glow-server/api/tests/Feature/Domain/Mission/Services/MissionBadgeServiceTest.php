<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionEventCategory;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Services\MissionBadgeService;
use App\Domain\Resource\Entities\MissionUnreceivedEventReward;
use App\Domain\Resource\Entities\MissionUnreceivedLimitedTermReward;
use App\Domain\Resource\Entities\MissionUnreceivedReward;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionBadgeServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionBadgeService $missionBadgeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionBadgeService = $this->app->make(MissionBadgeService::class);
    }

    public function test_fetchUnreceivedRewardData_報酬未受取情報を取得できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-04-14 15:00:00'); // JST: 2024-04-15 00:00:00 (月)。週の始まりは月曜日。
        $nowString = $now->toDateTimeString();
        $subDay = $now->copy()->subDay(); // JST: 2024-04-14 00:00:00 (日)
        $subDayString = $subDay->toDateTimeString();

        // usr
        foreach (MissionType::getNormals() as $missionType) {
            $mstIdPrefix = $missionType->value;
            $resettableAt = $this->getResettableAt($missionType, $now) ?? $now;

            // mst
            // criterion_type=CoinCollectとしているが特に意味はなく、指定idでミッションマスタデータを用意したいだけです
            $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::COIN_COLLECT, null, 10, '', 0);
            $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::COIN_COLLECT, null, 10, '', 0);
            $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 10, '', 0);
            $this->createMstMission($missionType, $mstIdPrefix.'4', MissionCriterionType::COIN_COLLECT, null, 10, '', 1);
            $this->createMstMission($missionType, $mstIdPrefix.'5', MissionCriterionType::COIN_COLLECT, null, 10, '', 1);
            $this->createMstMission($missionType, $mstIdPrefix.'lock1', MissionCriterionType::COIN_COLLECT, null, 10, '', 100);
            $this->createMstMission($missionType, $mstIdPrefix.'lock2', MissionCriterionType::COIN_COLLECT, null, 10, '', 100);
            $this->createMstMission($missionType, $mstIdPrefix.'lock3', MissionCriterionType::COIN_COLLECT, null, 10, '', 100);

            // usr
            // リセットされないデータ
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::UNCLEAR, 1, null, null, $now);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::CLEAR, 1, $nowString, null, $now);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::RECEIVED_REWARD, 1, $nowString, $nowString, $now);
            // リセットされるデータ
            // ※ リセットがないミッションタイプではリセットされないので、バッジ+1になる
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'4', MissionStatus::CLEAR, 1, $subDayString, null, $resettableAt);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'5', MissionStatus::RECEIVED_REWARD, 1, $subDayString, $subDayString, $resettableAt);
            // リセットされないが、マスタデータにないデータで、カウントに含まれない
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'mstNotFound', MissionStatus::CLEAR, 1, $nowString, null, $now);
            // 未開放なのでカウントに含まれない
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'lock1', MissionStatus::UNCLEAR, 1, $nowString, null, $now, MissionUnlockStatus::LOCK, 999);
            $this->createUsrMissionNormal($usrUserId, $missionType, $mstIdPrefix.'lock2', MissionStatus::CLEAR, 1, $nowString, null, $now, MissionUnlockStatus::LOCK, 999);
        }

        // Exercise
        $result = $this->missionBadgeService->fetchUnreceivedRewardData($usrUserId, $now);

        // Verify
        $this->assertInstanceOf(MissionUnreceivedReward::class, $result);
        $this->assertEquals(2, $result->getAchievementCount());
        $this->assertEquals(1, $result->getDailyCount());
        $this->assertEquals(1, $result->getWeeklyCount());
        $this->assertEquals(2, $result->getBeginnerCount());
    }

    public function test_fetchUnreceivedEventRewardCount_イベント報酬未受取情報を取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();

        MstEvent::factory()->createMany([
            ['id' => 'mst_event_id_1'],
            ['id' => 'mst_event_id_2']
        ]);
        MstMissionEvent::factory()->createMany([
            [
                'id' =>  'mst_event_mission_id_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_1',
                'event_category' => MissionEventCategory::ADVENT_BATTLE
            ],
            [
                'id' =>  'mst_event_mission_id_2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_2',
                'event_category' => null
            ],
        ]);
        MstMissionEventDaily::factory()->createMany([
            [
                'id' =>  'mst_event_daily_mission_id_1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mst_event_id' => 'mst_event_id_1',
            ],
            [
                'id' =>  'mst_event_daily_mission_id_2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 20,
                'mst_event_id' => 'mst_event_id_1',
            ],
        ]);
        UsrMissionEvent::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'mst_event_mission_id_1',
                'status' => MissionStatus::CLEAR,
                'is_open' => MissionUnlockStatus::OPEN,
                'cleared_at' => $now->subDay()->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now,
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => 'mst_event_mission_id_2',
                'status' => MissionStatus::CLEAR,
                'is_open' => MissionUnlockStatus::OPEN,
                'cleared_at' => $now->subDay()->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now,
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'mst_event_daily_mission_id_1',
                'status' => MissionStatus::CLEAR,
                'is_open' => MissionUnlockStatus::OPEN,
                'cleared_at' => $now->subDay()->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now,
            ],
            [
                // リセットされてバッジに入らない
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => 'mst_event_daily_mission_id_2',
                'status' => MissionStatus::CLEAR,
                'is_open' => MissionUnlockStatus::OPEN,
                'cleared_at' => $now->subDay()->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now->subDays(2)->toDateTimeString(),
            ],
        ]);

        // Exercise
        $result = $this->missionBadgeService->fetchUnreceivedEventRewardCount($usrUser->getId(), $now);

        // Verify
        $this->assertInstanceOf(MissionUnreceivedEventReward::class, $result);
        $this->assertEquals(2, $result->getEventCount()->sum(fn($value) => $value));
        $this->assertEquals(1, $result->getEventDailyCount()->sum(fn($value) => $value));
    }

    public function test_fetchUnreceivedLimitedTermRewardCount_期間限定報酬未受取情報を取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();

        MstMissionLimitedTerm::factory()->create([
            'id' => 'mst_mission_limited_term_1',
            'progress_group_key' => 'progress_group_key_1',
            'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
            'criterion_value' => null,
            'criterion_count' => 10,
            'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
        ]);
        UsrMissionLimitedTerm::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_mission_limited_term_id' => 'mst_mission_limited_term_1',
            'status' => MissionStatus::CLEAR,
            'is_open' => MissionUnlockStatus::OPEN,
            'cleared_at' => $now,
            'received_reward_at' => null,
            'latest_reset_at' => $now,
        ]);

        // Exercise
        $result = $this->missionBadgeService->fetchUnreceivedLimitedTermRewardCount($usrUser->getId(), $now);

        // Verify
        $this->assertInstanceOf(MissionUnreceivedLimitedTermReward::class, $result);
        $this->assertEquals(1, $result->getAdventBattleCount());
    }

    public function test_fetchUnreceivedRewardData_ボーナスポイントミッション完了時は他に受取可能なミッションがあってもバッジが0になる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-04-14 15:00:00'); // JST: 2024-04-15 00:00:00 (月)
        $nowString = $now->toDateTimeString();

        // daily用のミッション作成
        // 通常のミッション（受取可能）
        $this->createMstMission(MissionType::DAILY, 'daily_normal_1', MissionCriterionType::COIN_COLLECT, null, 10, '', 0);
        $this->createUsrMissionNormal($usrUserId, MissionType::DAILY, 'daily_normal_1', MissionStatus::CLEAR, 10, $nowString, null, $now);

        // ボーナスポイントミッション（完了済み、進捗値100、報酬受取済み）
        $this->createMstMission(MissionType::DAILY, 'daily_bonus_1', MissionCriterionType::MISSION_BONUS_POINT, null, 100, '', 0);
        $this->createUsrMissionNormal($usrUserId, MissionType::DAILY, 'daily_bonus_1', MissionStatus::RECEIVED_REWARD, 100, $nowString, $nowString, $now);

        // weekly用のミッション作成
        // 通常のミッション（受取可能）
        $this->createMstMission(MissionType::WEEKLY, 'weekly_normal_1', MissionCriterionType::COIN_COLLECT, null, 10, '', 0);
        $this->createUsrMissionNormal($usrUserId, MissionType::WEEKLY, 'weekly_normal_1', MissionStatus::CLEAR, 10, $nowString, null, $now);

        // ボーナスポイントミッション（完了済み、進捗値100、報酬受取済み）
        $this->createMstMission(MissionType::WEEKLY, 'weekly_bonus_1', MissionCriterionType::MISSION_BONUS_POINT, null, 100, '', 0);
        $this->createUsrMissionNormal($usrUserId, MissionType::WEEKLY, 'weekly_bonus_1', MissionStatus::RECEIVED_REWARD, 100, $nowString, $nowString, $now);

        // Exercise
        $result = $this->missionBadgeService->fetchUnreceivedRewardData($usrUserId, $now);

        // Verify
        $this->assertInstanceOf(MissionUnreceivedReward::class, $result);
        // ボーナスポイントミッション完了により、通常のミッションがあってもdaily/weeklyのバッジ数は0になる
        $this->assertEquals(0, $result->getDailyCount(), 'ボーナスポイントミッション完了時はdailyバッジが0になる');
        $this->assertEquals(0, $result->getWeeklyCount(), 'ボーナスポイントミッション完了時はweeklyバッジが0になる');
    }
}
