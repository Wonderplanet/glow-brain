<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Stage\UseCases;

use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageCleanupUseCase;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class StageCleanupUseCaseTest extends TestCase
{
    private StageCleanupUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(StageCleanupUseCase::class);
    }

    public function test_exec_進行中のセッションがある場合セッションがクローズされる()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstStageId = "stage_enhance_001";

        // 進行中のステージセッション（通常挑戦）を作成
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => StageSessionStatus::STARTED,
            'is_challenge_ad' => false,
            'opr_campaign_ids' => json_encode([]),
            'created_at' => $now->subMinutes(30)->toDateTimeString(),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser);
        $this->saveAll();

        // Verify
        // セッションがクローズされていることを確認
        $usrStageSession->refresh();
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());

        // レスポンスデータの確認（空であることを確認）
        $this->assertNotNull($result);
    }

    public function test_exec_進行中のセッションがない場合は例外が発生()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        // Exercise & Verify
        $this->expectException(\Throwable::class);
        
        $this->useCase->exec($currentUser);
    }

    public function test_exec_既にクローズされたセッションがある場合は例外が発生()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstStageId = "stage_enhance_007";

        // 既にクローズされたセッションを作成
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => StageSessionStatus::CLOSED,
            'is_challenge_ad' => false,
            'opr_campaign_ids' => json_encode([]),
            'created_at' => $now->subMinutes(40)->toDateTimeString(),
        ]);

        // Exercise & Verify
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        
        $this->useCase->exec($currentUser);
    }
}
