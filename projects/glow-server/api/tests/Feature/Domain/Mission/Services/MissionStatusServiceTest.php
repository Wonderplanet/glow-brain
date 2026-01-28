<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionStatusServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionStatusService $missionStatusService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionStatusService = $this->app->make(MissionStatusService::class);
    }

    public static function params_test_completeBeginnerMission_初心者ミッションが完了ステータスになる()
    {
        return [
            // ミッション開放からの経過日数、最終ミッションのステータス、初心者ミッション完了ステータス
            '完了になる' => [7, MissionStatus::RECEIVED_REWARD, true],
            '完了にならない 1つクリアステータス' => [7, MissionStatus::CLEAR, false],
            '完了にならない 1つ未クリアステータス' => [7, MissionStatus::UNCLEAR, false],
        ];
    }

    #[DataProvider('params_test_completeBeginnerMission_初心者ミッションが完了ステータスになる')]
    public function test_completeBeginnerMission_初心者ミッションが完了ステータスになる(
        int $unlockedDays,
        MissionStatus $lastMissionStatus,
        bool $expected,
    ): void {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        MstMissionBeginner::factory()->createMany([
            [
                'id' => 'beginner1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'criterion_value' => 'stage1', 'criterion_count' => 1,
                'unlock_day' => 1,
            ],
            [
                'id' => 'beginner2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 100,
                'unlock_day' => 3,
            ],
            [
                'id' => 'beginner7',
                'criterion_type' => MissionCriterionType::DEFEAT_ENEMY_COUNT, 'criterion_value' => null, 'criterion_count' => 100,
                'unlock_day' => 7,
            ],
        ]);

        // usr
        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::FULLY_UNLOCKED->value, // 全ミッション開放済で完了判定処理を進められる状態に設定
            'mission_unlocked_at' => $now->subdays($unlockedDays)->toDateTimeString(),
        ]);

        $this->createUsrMissionNormal($usrUserId, MissionType::BEGINNER, 'beginner1', MissionStatus::RECEIVED_REWARD, 1, $now, $now, $now);
        $this->createUsrMissionNormal($usrUserId, MissionType::BEGINNER, 'beginner2', MissionStatus::RECEIVED_REWARD, 100, $now, $now, $now);
        switch ($lastMissionStatus) {
            case MissionStatus::RECEIVED_REWARD:
                $this->createUsrMissionNormal($usrUserId, MissionType::BEGINNER, 'beginner7', MissionStatus::RECEIVED_REWARD, 100, $now, $now, $now);
                break;
            case MissionStatus::CLEAR:
                $this->createUsrMissionNormal($usrUserId, MissionType::BEGINNER, 'beginner7', MissionStatus::CLEAR, 100, $now, null, $now);
                break;
            case MissionStatus::UNCLEAR:
                $this->createUsrMissionNormal($usrUserId, MissionType::BEGINNER, 'beginner7', MissionStatus::UNCLEAR, 0, null, null, $now);
                break;
        }

        // Exercise
        $this->missionStatusService->completeBeginnerMission($usrUserId);
        $this->saveAll();

        // Verify
        $usrMissionStatus = UsrMissionStatus::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrMissionStatus);
        $this->assertEquals($expected, $usrMissionStatus->isBeginnerMissionCompleted());
    }
}
