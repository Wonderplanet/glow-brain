<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\UseCases\AdventBattleCleanupUseCase;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class AdventBattleCleanupUseCaseTest extends TestCase
{
    private AdventBattleCleanupUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AdventBattleCleanupUseCase::class);
    }

    public function test_exec_正常実行_進行中のセッションがある場合()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "advent_battle_001";

        // マスターデータをセットアップ
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'challengeable_count' => 3,
            'ad_challengeable_count' => 1,
        ]);

        // ユーザーの降臨バトルデータを作成
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 2, // 合計挑戦回数
            'reset_challenge_count' => 2, // 通常挑戦回数
            'reset_ad_challenge_count' => 1, // 広告視聴挑戦回数
        ]);

        // 進行中の降臨バトルセッションを作成
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED,
            'party_no' => 1,
            'is_challenge_ad' => false,
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrAdventBattleSession = UsrAdventBattleSession::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());

        // レスポンスデータの確認（空であることを確認）
        $this->assertNotNull($result);
    }

    public function test_exec_広告挑戦セッションがある場合()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId);
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "advent_battle_002";

        // マスターデータをセットアップ
        MstAdventBattle::factory()->create([
            'id' => $mstAdventBattleId,
            'challengeable_count' => 3,
            'ad_challengeable_count' => 1,
        ]);

        // ユーザーの降臨バトルデータを作成
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 3, // 合計挑戦回数
            'reset_challenge_count' => 2, // 通常挑戦回数
            'reset_ad_challenge_count' => 1, // 広告視聴挑戦回数
        ]);

        // 進行中の広告挑戦セッションを作成
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED,
            'party_no' => 1,
            'is_challenge_ad' => true,
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrAdventBattleSession = UsrAdventBattleSession::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());

        // レスポンスデータの確認（空であることを確認）
        $this->assertNotNull($result);
    }

    public function test_exec_進行中のセッションがない場合は例外が発生()
    {
        // Setup
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

        $mstAdventBattleId = "advent_battle_006";

        // 既にクローズされたセッションを作成
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::CLOSED,
            'party_no' => 1,
            'is_challenge_ad' => false,
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->useCase->exec($currentUser);
    }
}
