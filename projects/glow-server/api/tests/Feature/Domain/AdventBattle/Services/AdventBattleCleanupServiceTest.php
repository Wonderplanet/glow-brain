<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Services\AdventBattleCleanupService;
use Tests\TestCase;

class AdventBattleCleanupServiceTest extends TestCase
{
    private AdventBattleCleanupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AdventBattleCleanupService::class);
    }

    public function test_cleanup_通常挑戦_セッションがクローズされることを確認()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $mstAdventBattleId = 'advent_battle_normal';

        // 降臨バトルセッション（通常挑戦）を作成
        $usrAdventBattleSession = UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED,
            'is_challenge_ad' => false,
        ]);

        // Exercise
        $this->service->cleanup($usrUserId);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrAdventBattleSession->refresh();
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
    }

    public function test_cleanup_広告挑戦_セッションがクローズされることを確認()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $mstAdventBattleId = 'advent_battle_ad';

        // 降臨バトルセッション（広告挑戦）を作成
        $usrAdventBattleSession = UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED,
            'is_challenge_ad' => true,
        ]);

        // Exercise
        $this->service->cleanup($usrUserId);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrAdventBattleSession->refresh();
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
    }

    public function test_cleanup_セッションが存在しない場合は例外が発生()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->service->cleanup($usrUserId);
    }

    public function test_cleanup_セッションがクローズ済みの場合は例外が発生()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstAdventBattleId = 'advent_battle_closed';

        // すでにクローズ済みのセッションを作成
        $usrAdventBattleSession = UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::CLOSED, // すでにクローズ済み
            'is_challenge_ad' => false,
        ]);

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->service->cleanup($usrUserId);
    }


}
