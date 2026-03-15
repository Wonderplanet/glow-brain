<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Common\Services;

use App\Domain\Common\Enums\ContentMaintenanceType;
use App\Domain\Common\Services\ContentMaintenanceService;
use App\Domain\Common\Entities\ContentMaintenanceCheckResult;
use App\Domain\Resource\Mng\Models\MngContentClose;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Stage\Enums\QuestType;
use Illuminate\Http\Request;
use Tests\TestCase;

class ContentMaintenanceServiceTest extends TestCase
{
    private ContentMaintenanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(ContentMaintenanceService::class);
    }

    public function test_checkMaintenanceStatus_コンテンツタイプが取得できない場合()
    {
        // Setup
        $request = Request::create('/unknown/path', 'GET');

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertInstanceOf(ContentMaintenanceCheckResult::class, $result);
        $this->assertNull($result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertNull($result->contentId);
    }

    public function test_checkMaintenanceStatus_コインクエストの判定()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 経験値クエスト（ENHANCE）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_enhance_001',
            'quest_type' => QuestType::ENHANCE->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_enhance_001',
            'mst_quest_id' => 'quest_enhance_001',
        ]);

        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'stage_enhance_001',
        ]);
        
        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::ENHANCE_QUEST, $result->contentType);
        $this->assertTrue($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertEquals('stage_enhance_001', $result->contentId);
    }

    public function test_checkMaintenanceStatus_コインクエスト以外の判定()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // コインクエスト（NORMAL）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_coin_001',
            'quest_type' => QuestType::NORMAL->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_coin_001',
            'mst_quest_id' => 'quest_coin_001',
        ]);

        // ユーザーのステージセッションを作成（コインクエストが実行中であることを示す）
        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_coin_001',
        ]);

        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'stage_coin_001',
        ]);
        
        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::ENHANCE_QUEST, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest); // ENHANCEタイプではない
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertEquals('stage_coin_001', $result->contentId);
    }

    public function test_checkMaintenanceStatus_全体メンテナンス中()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 降臨バトル全体メンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null, // 全体メンテナンス
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/advent_battle/start', 'POST');

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::ADVENT_BATTLE, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertTrue($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertNull($result->contentId); // AdventBattleはIDパラメータなし
    }

    public function test_checkMaintenanceStatus_個別メンテナンス中()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 特定のガチャのみメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_premium_001',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_premium_001'
        ]);

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::GACHA, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertTrue($result->isUnderContentMaintenanceByContentId);
        $this->assertEquals('gacha_premium_001', $result->contentId);
    }

    public function test_checkMaintenanceStatus_個別メンテナンス対象外()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 別のガチャ（gacha_001）のみメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_001',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_002' // 対象外
        ]);

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::GACHA, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId); // 対象外なのでfalse
        $this->assertEquals('gacha_002', $result->contentId);
    }

    public function test_checkMaintenanceStatus_メンテナンス時間外()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス時間外
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::PVP->value,
            'content_id' => null,
            'start_at' => $now->addHours(1)->toDateTimeString(), // 未来
            'end_at' => $now->addHours(2)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/pvp/start', 'POST');

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::PVP, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance); // 時間外なのでfalse
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertNull($result->contentId);
    }

    public function test_checkMaintenanceStatus_無効なメンテナンス設定()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 無効なメンテナンス設定
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::PVP->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => false, // 無効
        ]);

        $request = Request::create('/api/pvp/start', 'POST');

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::PVP, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance); // 無効なので無視される
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertNull($result->contentId);
    }

    public function test_checkMaintenanceStatus_複数のメンテナンス設定()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 全体メンテナンス（有効）
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => null, // 全体メンテナンス
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 個別メンテナンス（有効）
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_special_001',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_special_001'
        ]);

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify - 両方ともメンテナンス中
        $this->assertEquals(ContentMaintenanceType::GACHA, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertTrue($result->isUnderContentMaintenance);
        $this->assertTrue($result->isUnderContentMaintenanceByContentId);
        $this->assertEquals('gacha_special_001', $result->contentId);
    }

    public function test_checkMaintenanceStatus_IDパラメータなしのコンテンツ()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // PVPは個別IDを持たないコンテンツ
        $request = Request::create('/api/pvp/match', 'POST');

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::PVP, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest);
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId); // IDがないので個別チェックされない
        $this->assertNull($result->contentId); // PVPはIDパラメータなし
    }

    public function test_checkMaintenanceStatus_ステージが存在しない場合()
    {
        // Setup
        $user = $this->createUsrUser();
        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'nonexistent_stage'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Exercise
        $result = $this->service->checkMaintenanceStatus($request);

        // Verify
        $this->assertEquals(ContentMaintenanceType::ENHANCE_QUEST, $result->contentType);
        $this->assertFalse($result->isEnhanceQuest); // ステージが存在しないのでfalse
        $this->assertFalse($result->isUnderContentMaintenance);
        $this->assertFalse($result->isUnderContentMaintenanceByContentId);
        $this->assertEquals('nonexistent_stage', $result->contentId);
    }
}
