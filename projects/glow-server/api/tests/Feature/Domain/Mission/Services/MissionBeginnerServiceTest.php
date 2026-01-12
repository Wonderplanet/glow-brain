<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Mission\Services;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Models\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Services\MissionBeginnerService;
use App\Domain\Resource\Mst\Models\MstMissionBeginner;
use App\Domain\Resource\Usr\Models\UsrUser;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionBeginnerServiceTest extends TestCase
{
    use RefreshDatabase;

    private MissionBeginnerService $missionBeginnerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->missionBeginnerService = app(MissionBeginnerService::class);
    }

    /**
     * 初心者ミッションが完了済みの場合、何もせずに終了することを確認
     */
    public function testUnlockTodayMissions_WhenBeginnerMissionCompleted_DoesNothing(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 初心者ミッションステータスをCOMPLETEDに設定
        $usrMissionStatus = UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::COMPLETED->value,
            'mission_unlocked_at' => CarbonImmutable::now()->subDays(10)->toDateTimeString(),
        ]);
        
        // 実行前のupdated_atを記録
        $beforeUpdatedAt = $usrMissionStatus->updated_at;

        // 初心者ミッションマスターデータを作成
        MstMissionBeginner::factory()->create([
            'unlock_day' => 1,
            'sort_order' => 1,
        ]);

        // 実行前のUsrMissionNormalの数を記録
        $beforeMissionCount = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->count();

        // Act
        $this->missionBeginnerService->unlockTodayMissions($usrUserId);
        $this->saveAll();

        // Assert
        // COMPLETED状態の場合、新しいミッションが開放されないことを確認
        $afterMissionCount = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->count();
        $this->assertEquals($beforeMissionCount, $afterMissionCount, 'COMPLETED状態では新しいミッションが開放されないこと');

        // ステータスが変更されていないことを確認
        $usrMissionStatus = UsrMissionStatus::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(MissionBeginnerStatus::COMPLETED->value, $usrMissionStatus->beginner_mission_status);
        
        // updated_atが更新されていないことを確認
        $this->assertEquals($beforeUpdatedAt->toDateTimeString(), $usrMissionStatus->updated_at->toDateTimeString(), '更新されていないことを確認');
    }

    /**
     * 初心者ミッションが完全開放済みの場合、何もせずに終了することを確認
     */
    public function testUnlockTodayMissions_WhenBeginnerMissionFullyUnlocked_DoesNothing(): void
    {
        // Arrange
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // 初心者ミッションステータスをFULLY_UNLOCKEDに設定
        $usrMissionStatus = UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::FULLY_UNLOCKED->value,
            'mission_unlocked_at' => CarbonImmutable::now()->subDays(10)->toDateTimeString(),
        ]);
        
        // 実行前のupdated_atを記録
        $beforeUpdatedAt = $usrMissionStatus->updated_at;

        // 初心者ミッションマスターデータを作成
        MstMissionBeginner::factory()->create([
            'unlock_day' => 1,
            'sort_order' => 1,
        ]);

        // 実行前のUsrMissionNormalの数を記録
        $beforeMissionCount = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->count();

        // Act
        $this->missionBeginnerService->unlockTodayMissions($usrUserId);
        $this->saveAll();

        // Assert
        // FULLY_UNLOCKED状態の場合、新しいミッションが開放されないことを確認
        $afterMissionCount = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->count();
        $this->assertEquals($beforeMissionCount, $afterMissionCount, 'FULLY_UNLOCKED状態では新しいミッションが開放されないこと');

        // ステータスが変更されていないことを確認
        $usrMissionStatus = UsrMissionStatus::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(MissionBeginnerStatus::FULLY_UNLOCKED->value, $usrMissionStatus->beginner_mission_status);
        
        // updated_atが更新されていないことを確認
        $this->assertEquals($beforeUpdatedAt->toDateTimeString(), $usrMissionStatus->updated_at->toDateTimeString(), '更新されていないことを確認');
    }
}
