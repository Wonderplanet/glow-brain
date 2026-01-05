<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonus;
use App\Domain\Mission\Services\MissionEventDailyBonusUpdateService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionEventDailyBonusUpdateServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionEventDailyBonusUpdateService $missionEventDailyBonusUpdateService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionEventDailyBonusUpdateService = app(MissionEventDailyBonusUpdateService::class);
    }

    private function setUpFixtures(string $now): CarbonImmutable
    {
        $now = $this->fixTime($now);
        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'reward_group_1',
            ],
            [
                'id' => 'bonus_2',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => 'reward_group_2',
            ],
            [
                'id' => 'bonus_3',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 3,
                'mst_mission_reward_group_id' => 'reward_group_3',
            ],
        ]);

        MstMissionEventDailyBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'mst_event_id' => 'event_1',
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'id' => 'schedule_2',
                'mst_event_id' => 'event_2',
                'start_at' => $now->addDays(5)->toDateTimeString(),
                'end_at' => $now->addDays(7)->toDateTimeString(),
            ],
        ]);

        MstMissionReward::factory()->createMany([
            [
                'id' => 'reward_1',
                'group_id' => 'reward_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => 'item_1',
                'resource_amount' => 1,
            ],
            [
                'id' => 'reward_2',
                'group_id' => 'reward_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'id' => 'reward_3',
                'group_id' => 'reward_group_3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100000],
        ]);

        return $now;
    }

    public function test_updateStatuses_ステータス更新ができている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixtures('2024-05-13 19:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDay());
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2);
        $this->saveAll();
        $now3 = $this->fixTime($now2->addDay()->subSecond());
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now3);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_1',
            isExist:true,
            isClear:true,
            clearedAt: $now1->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now1->toDateTimeString(),
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_2',
            isExist:true,
            isClear:true,
            clearedAt: $now2->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now2->toDateTimeString(),
        );

        // 未開放であることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_3',
            isExist:false,
            isClear:false,
            clearedAt: null,
            isReceiveReward:false,
            receivedRewardAt: null,
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(11, $usrParameter->getCoin());
    }

    public function test_updateStatuses_1日ログインしなかった場合にそのログインしてない分はもらえないこと()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDays(2));
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_1',
            isExist:true,
            isClear:true,
            clearedAt: $now1->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now1->toDateTimeString(),
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_2',
            isExist:true,
            isClear:true,
            clearedAt: $now2->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now2->toDateTimeString(),
        );

        // 未開放であることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_3',
            isExist:false,
            isClear:false,
            clearedAt: null,
            isReceiveReward:false,
            receivedRewardAt: null,
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(11, $usrParameter->getCoin());
    }

    public function test_updateStatuses_イベント期間外の場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        $now = $now->addDays(5);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 未獲得であることを確認
        $missionIds = ['bonus_1', 'bonus_2', 'bonus_3'];
        foreach ($missionIds as $missionId) {
            $this->checkUsrMissionStatus(
                $usrUserId,
                $missionId,
                isExist:false,
                isClear:false,
                clearedAt: null,
                isReceiveReward:false,
                receivedRewardAt: null,
            );
        }
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $usrParameter->getCoin());
    }

    public function test_updateStatuses_報酬が受け取れていることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();
        $now = $this->fixTime($now->addDay());
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();
        $now = $this->fixTime($now->addDay());
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(111, $usrParameter->getCoin());
    }

    public function test_updateStatuses_期間内で報酬を全て受け取った後の期間もログインできること()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->setUpFixtures('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
        $this->saveAll();
        for ($i = 0; $i < 4; $i++) {
            $now = $this->fixTime($now->addDay());
            $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now);
            $this->saveAll();
        }

        // Verify

        // エラーにならず、報酬受け取り済みであることを確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(111, $usrParameter->getCoin());
    }

    private function setUpFixturesMultiEvents(string $now): CarbonImmutable
    {
        $now = $this->fixTime($now);
        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'reward_group_1',
            ],
            [
                'id' => 'bonus_2',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => 'reward_group_2',
            ],
            [
                'id' => 'bonus_3',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 3,
                'mst_mission_reward_group_id' => 'reward_group_3',
            ],
            [
                'id' => 'bonus_4',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_2',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'reward_group_1',
            ],
            [
                'id' => 'bonus_5',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_2',
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => 'reward_group_2',
            ],
            [
                'id' => 'bonus_6',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_2',
                'login_day_count' => 3,
                'mst_mission_reward_group_id' => 'reward_group_3',
            ],
        ]);

        MstMissionEventDailyBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'mst_event_id' => 'event_1',
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
            [
                'id' => 'schedule_2',
                'mst_event_id' => 'event_2',
                'start_at' => $now->toDateTimeString(),
                'end_at' => $now->addDays(4)->toDateTimeString(),
            ],
        ]);

        MstMissionReward::factory()->createMany([
            [
                'id' => 'reward_1',
                'group_id' => 'reward_group_1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => 'item_1',
                'resource_amount' => 1,
            ],
            [
                'id' => 'reward_2',
                'group_id' => 'reward_group_2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 10,
            ],
            [
                'id' => 'reward_3',
                'group_id' => 'reward_group_3',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100000],
        ]);

        return $now;
    }

    public function test_updateStatuses_複数イベントログボが開催されている場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now1 = $this->setUpFixturesMultiEvents('2024-05-14 15:00:00');
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => 0,
        ]);

        // Exercice
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now1);
        $this->saveAll();
        $now2 = $this->fixTime($now1->addDays(2));
        $this->missionEventDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now2);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_1',
            isExist:true,
            isClear:true,
            clearedAt: $now1->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now1->toDateTimeString(),
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_2',
            isExist:true,
            isClear:true,
            clearedAt: $now2->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now2->toDateTimeString(),
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_4',
            isExist:true,
            isClear:true,
            clearedAt: $now1->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now1->toDateTimeString(),
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_5',
            isExist:true,
            isClear:true,
            clearedAt: $now2->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now2->toDateTimeString(),
        );

        // 未開放であることを確認
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_3',
            isExist:false,
            isClear:false,
            clearedAt: null,
            isReceiveReward:false,
            receivedRewardAt: null,
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'bonus_6',
            isExist:false,
            isClear:false,
            clearedAt: null,
            isReceiveReward:false,
            receivedRewardAt: null,
        );
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(22, $usrParameter->getCoin());
    }

    private function checkUsrMissionStatus(
        string $usrUserId,
        string $mstMissionId,
        bool $isExist,
        bool $isClear,
        ?string $clearedAt,
        bool $isReceiveReward,
        ?string $receivedRewardAt
    ): void {
        $usrMission = UsrMissionEventDailyBonus::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_event_daily_bonus_id', $mstMissionId)
            ->first();

        if ($isExist) {
            $this->assertNotNull($usrMission, 'not exist');
        } else {
            $this->assertNull($usrMission, 'exist');
            return;
        }

        if ($isClear) {
            $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->isClear(), 'clear');
            $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'clearAt');
        } else {
            $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->getStatus(), 'unclear');
            $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'clearAt');
        }

        if ($isReceiveReward) {
            $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $usrMission->getStatus(), 'reward received');
            $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'receivedRewardAt');
        }
    }
}
