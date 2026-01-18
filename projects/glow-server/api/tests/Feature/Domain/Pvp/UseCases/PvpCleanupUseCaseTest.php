<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\UseCases;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpCleanupUseCase;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\User\Models\UsrUserProfile;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class PvpCleanupUseCaseTest extends TestCase
{
    private PvpCleanupUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(PvpCleanupUseCase::class);
    }

    public function test_exec_正常実行_進行中のセッションがある場合()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $currentUser = new CurrentUser($usrUserId);

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        // マスターデータをセットアップ
        $mstPvp = MstPvp::factory()->create([
            'item_challenge_cost_amount' => 1,
            'max_daily_challenge_count' => 5,
            'max_daily_item_challenge_count' => 5,
        ]);

        MstPvpRank::factory()->create([
            'rank_class_type' => 'Bronze',
            'rank_class_level' => 1,
            'required_lower_score' => 0,
            'win_add_point' => 10,
            'lose_sub_point' => 5,
        ]);

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => $now->subDays(1)->toDateTimeString(),
            'end_at' => $now->addDays(7)->toDateTimeString(),
        ]);

        // ユーザーのPVPデータを作成
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 100,
            'daily_remaining_challenge_count' => 3,
            'daily_remaining_item_challenge_count' => 4,
        ]);

        // ユーザープロフィールを作成
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // 進行中のPVPセッションを作成
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED->value,
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrPvpSession->refresh();
        $this->assertEquals(PvpSessionStatus::CLOSED, $usrPvpSession->getIsValid());

        // レスポンスデータが正常に返されることを確認（空であることを確認）
        $this->assertNotNull($result);
    }

    public function test_exec_進行中のセッションがない場合は例外が発生()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $currentUser = new CurrentUser($usrUserId);

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->useCase->exec($currentUser);
    }

    public function test_exec_既にクローズされたセッションがある場合は例外が発生()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $currentUser = new CurrentUser($usrUserId);

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        // 既にクローズされたPVPセッションを作成
        $usrPvpSession = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::CLOSED->value,
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->useCase->exec($currentUser);
    }
}
