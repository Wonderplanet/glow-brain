<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Services\MissionDailyBonusUpdateService;
use App\Domain\Resource\Entities\UserLoginCount;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\User\Constants\UserConstant;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionDailyBonusUpdateServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionDailyBonusUpdateService $missionDailyBonusUpdateService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionDailyBonusUpdateService = app(MissionDailyBonusUpdateService::class);
    }

    private function setUpFixtures(): void
    {
        MstMissionDailyBonus::factory()->createMany([
            [
                'id' => 'daily_bonus-1',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 1,
            ],
            [
                'id' => 'daily_bonus-2',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 2,
            ],
            [
                'id' => 'daily_bonus-5',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 5,
            ],
            [
                'id' => 'daily_bonus-7',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 7,
            ],
        ]);
    }

    public function test_updateStatuses_ステータス更新ができている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $beforeLoginAt = $now->copy()->subDay();

        $this->setUpFixtures();

        $userLoginCount = new UserLoginCount(
            beforeLoginAt: $beforeLoginAt->toDateTimeString(),
            currentLoginAt: $now->toDateTimeString(),
            isFirstLoginToday: true,
            loginDayCount: 2,
            beforeLoginContinueDayCount: 1,
            loginContinueDayCount: 2,
            comebackDayCount: 0,
        );

        // Exercice
        $this->missionDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, $userLoginCount);
        $this->saveAll();

        // Verify

        // 報酬受け取り済みであることを確認
        $receivedMstMissionIds = ['daily_bonus-1', 'daily_bonus-2'];
        foreach ($receivedMstMissionIds as $mstMissionId) {
            $this->checkUsrMissionStatus(
                $usrUserId,
                $mstMissionId,
                isExist:true,
                isClear:true,
                clearedAt: $now->toDateTimeString(),
                isReceiveReward:true,
                receivedRewardAt: $now->toDateTimeString(),
            );
        }
        // 未開放であることを確認
        $lockedMstMissionIds = ['daily_bonus-5'];
        foreach ($lockedMstMissionIds as $mstMissionId) {
            $this->checkUsrMissionStatus(
                $usrUserId,
                $mstMissionId,
                isExist:false,
                isClear:false,
                clearedAt: null,
                isReceiveReward:false,
                receivedRewardAt: null,
            );
        }
    }

    public function test_updateStatuses_連続ログイン8日目のデイリーボーナス進捗は1日目のみクリアになっている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $beforeLoginAt = $now->copy()->subDay();

        $this->setUpFixtures();

        UsrMissionDailyBonus::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'daily_bonus-1',
                'status' => MissionStatus::RECEIVED_REWARD->value,
                'cleared_at' => $now->copy()->subDays(7),
                'received_reward_at' => $now->copy()->subDays(7),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'daily_bonus-2',
                'status' => MissionStatus::RECEIVED_REWARD->value,
                'cleared_at' => $now->copy()->subDays(6),
                'received_reward_at' => $now->copy()->subDays(6),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'daily_bonus-5',
                'status' => MissionStatus::RECEIVED_REWARD->value,
                'cleared_at' => $now->copy()->subDays(3),
                'received_reward_at' => $now->copy()->subDays(3),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'daily_bonus-7',
                'status' => MissionStatus::RECEIVED_REWARD->value,
                'cleared_at' => $now->copy()->subDays(1),
                'received_reward_at' => $now->copy()->subDays(1),
            ],
        ]);

        $userLoginCount = new UserLoginCount(
            beforeLoginAt: $beforeLoginAt->toDateTimeString(),
            currentLoginAt: $now->toDateTimeString(),
            isFirstLoginToday: true,
            loginDayCount: 8,
            beforeLoginContinueDayCount: 7,
            loginContinueDayCount: 8,
            comebackDayCount: 0,
        );

        // Exercice
        $this->missionDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, $userLoginCount);
        $this->saveAll();

        // Verify
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::DAILY_BONUS);
        // 1日目だけ達成になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'daily_bonus-1',
            isExist:true,
            isClear:true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now->toDateTimeString(),
        );
        $this->checkMissionStatus($usrMissions, 'daily_bonus-2', isExist:true, isClear:false);
        $this->checkMissionStatus($usrMissions, 'daily_bonus-5', isExist:true, isClear:false);
        $this->checkMissionStatus($usrMissions, 'daily_bonus-7', isExist:true, isClear:false);
    }

    public function test_updateStatuses_新規アカウント登録日最初のログインで自動受取してステータス更新ができている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $beforeLoginAt = $now->copy()->subDay();

        $this->setUpFixtures();

        $userLoginCount = new UserLoginCount(
            beforeLoginAt: $now->toDateTimeString(), // 新規アカウント登録時はbeforeとcurrentが同じになる
            currentLoginAt: $now->toDateTimeString(),
            isFirstLoginToday: true,
            loginDayCount: 1,
            beforeLoginContinueDayCount: 1,
            loginContinueDayCount: 1,
            comebackDayCount: 0,
        );

        // Exercice
        $this->missionDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, $userLoginCount);
        $this->saveAll();

        // Verify
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::DAILY_BONUS);
        $this->checkUsrMissionStatus(
            $usrUserId,
            'daily_bonus-1',
            isExist:true,
            isClear:true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward:true,
            receivedRewardAt: $now->toDateTimeString(),
        );
        $this->checkMissionStatus($usrMissions, 'daily_bonus-2', isExist:false, isClear:false);
        $this->checkMissionStatus($usrMissions, 'daily_bonus-5', isExist:false, isClear:false);
    }

    public function test_updateStatuses_新規アカウント登録から2日目ログインで2日目報酬のみ受取可になっている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-05-14 15:00:00');
        $nowString = $now->toDateTimeString();
        $beforeLoginAt = '2024-05-13 15:00:00';

        $this->setUpFixtures();

        $userLoginCount = new UserLoginCount(
            beforeLoginAt: $beforeLoginAt,
            currentLoginAt: $now,
            isFirstLoginToday: true,
            loginDayCount: 2,
            beforeLoginContinueDayCount: 1,
            loginContinueDayCount: 2,
            comebackDayCount: 0,
        );
        // 1日目報酬受け取り済み
        UsrMissionDailyBonus::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'daily_bonus-1',
                'status' => MissionStatus::RECEIVED_REWARD->value,
                'cleared_at' => $beforeLoginAt,
                'received_reward_at' => $beforeLoginAt,
            ],
        ]);

        // Exercice
        $this->missionDailyBonusUpdateService->updateStatuses($usrUserId, UserConstant::PLATFORM_IOS, $now, $userLoginCount);
        $this->saveAll();

        // Verify
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::DAILY_BONUS);
        // 1日目は報酬受け取り済み
        $this->checkUsrMissionStatus(
            $usrUserId,
            'daily_bonus-1',
            isExist:true,
            isClear:true,
            clearedAt: $beforeLoginAt,
            isReceiveReward:true,
            receivedRewardAt: $beforeLoginAt,
        );
        // 2日目はクリア済み
        $this->checkUsrMissionStatus(
            $usrUserId,
            'daily_bonus-2',
            isExist:true,
            isClear:true,
            clearedAt: $nowString,
            isReceiveReward:false,
            receivedRewardAt: null,
        );
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
        $usrMission = UsrMissionDailyBonus::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_daily_bonus_id', $mstMissionId)
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
