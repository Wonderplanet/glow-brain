<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Enums\ContentMaintenanceType;
use App\Domain\Common\Services\ContentMaintenanceTypeMapper;
use App\Domain\Resource\Mng\Models\MngContentClose;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Stage\Enums\QuestType;
use App\Http\Middleware\ContentMaintenanceCheck;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ContentMaintenanceCheckTest extends TestCase
{
    private ContentMaintenanceCheck $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = app(ContentMaintenanceCheck::class);
    }

    public function test_handle_コンテンツタイプが取得できない場合はスルー()
    {
        // Setup - 未知のパスでコンテンツタイプが取得できない
        $request = Request::create('/unknown/path', 'GET');

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function test_handle_コイン獲得クエスト以外はスルー()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス中のコンテンツを作成
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ENHANCE_QUEST->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_001',
            'quest_type' => QuestType::NORMAL->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_001',
            'mst_quest_id' => 'quest_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_001',
        ]);

        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'stage_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify - 経験値クエストはスルー
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function test_handle_コイン獲得クエストはブロック()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス中のコンテンツを作成
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ENHANCE_QUEST->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_exp_001',
            'quest_type' => QuestType::ENHANCE->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_exp_001',
            'mst_quest_id' => 'quest_exp_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_exp_001',
        ]);

        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'stage_exp_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CONTENT_MAINTENANCE);

        $this->middleware->handle($request, $next);
    }

    public function test_handle_コイン獲得クエストでも設定がない場合はスルー()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_exp_001',
            'quest_type' => QuestType::ENHANCE->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_exp_001',
            'mst_quest_id' => 'quest_exp_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_exp_001',
        ]);

        $request = Request::create('/api/stage/start', 'POST', [
            'mstStageId' => 'stage_exp_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify - 経験値クエストはスルー
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    /**
     * @test
     * @dataProvider cleanupNeededApiProvider
     */
    public function test_handle_cleanupが必要なAPIはブロックし特定のエラーコードを返す(string $apiPath)
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス中のコンテンツを作成
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::PVP->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ENHANCE_QUEST->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_exp_001',
            'quest_type' => QuestType::ENHANCE->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_exp_001',
            'mst_quest_id' => 'quest_exp_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_exp_001',
        ]);

        $request = Request::create($apiPath, 'POST', [
            'mstStageId' => 'stage_exp_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CONTENT_MAINTENANCE_NEED_CLEANUP);

        $this->middleware->handle($request, $next);
    }

    /**
     * @return array
     */
    public static function cleanupNeededApiProvider(): array
    {
        $reflection = new \ReflectionClass(ContentMaintenanceCheck::class);
        $needCleanupApiList = $reflection->getConstant('NEED_CLEANUP_API_LIST');
        return array_map(fn($path) => [$path], array_keys($needCleanupApiList));
    }

    public function test_handle_全体メンテナンス中はブロック()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // ADVENT_BATTLE全体メンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null, // 全体メンテナンス
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/advent_battle/start', 'POST');

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CONTENT_MAINTENANCE);

        $this->middleware->handle($request, $next);
    }

    public function test_handle_メンテナンス時間外はスルー()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス時間外
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null,
            'start_at' => $now->addHours(1)->toDateTimeString(), // 未来
            'end_at' => $now->addHours(2)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/advent_battle/start', 'POST');

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function test_handle_無効なメンテナンスは無視()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 無効なメンテナンス設定
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => false, // 無効
        ]);

        $request = Request::create('/api/advent_battle/start', 'POST');

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify - 無効なメンテナンスは無視される
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function test_handle_ガチャ個別メンテナンス対象外のIDはスルー()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 特定のガチャ（gacha_001）のみメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_001', // gacha_001のみメンテナンス
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 別のガチャ（gacha_002）にアクセス
        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_002' // メンテナンス対象外
        ]);

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify - メンテナンス対象外のガチャIDなので正常処理
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    public function test_handle_ガチャ個別メンテナンス中はブロック()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 特定のガチャ（gacha_premium_001）がメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_premium_001',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // メンテナンス中のガチャにアクセス
        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_premium_001' // メンテナンス対象
        ]);

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CONTENT_MAINTENANCE);

        $this->middleware->handle($request, $next);
    }

    public function test_handle_複数のガチャ個別メンテナンス設定で対象外IDはスルー()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 複数のガチャ個別メンテナンス設定
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_limited_001',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::GACHA->value,
            'content_id' => 'gacha_limited_003',
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // メンテナンス対象外のガチャ（gacha_normal_001）にアクセス
        $request = Request::create('/api/gacha/draw', 'POST', [
            'oprGachaId' => 'gacha_normal_001' // gacha_limited_001とgacha_limited_003はメンテナンス中だが、gacha_normal_001は対象外
        ]);

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify - メンテナンス対象外のガチャIDなので正常処理
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $response->getContent());
    }

    // cleanup用test

    public function test_handle_メンテナンス中の場合でのcleanupはスルー()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 降臨バトルメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/advent_battle/cleanup', 'POST');

        $next = function ($req) {
            return new Response('cleanup success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('cleanup success', $response->getContent());
    }

    public function test_handle_メンテナンス中でない場合のcleanupはブロック()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // メンテナンス時間外
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ADVENT_BATTLE->value,
            'content_id' => null,
            'start_at' => $now->addHours(1)->toDateTimeString(), // 未来
            'end_at' => $now->addHours(2)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/advent_battle/cleanup', 'POST');

        $next = function ($req) {
            return new Response('cleanup success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);

        $this->middleware->handle($request, $next);
    }

    public function test_handle_コインクエスト以外でのcleanupはブロック()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // ステージメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ENHANCE_QUEST->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_exp_001',
            'quest_type' => QuestType::NORMAL->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_exp_001',
            'mst_quest_id' => 'quest_exp_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_exp_001',
        ]);

        $request = Request::create('/api/stage/cleanup', 'POST', [
            'mstStageId' => 'stage_exp_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('cleanup success', 200);
        };

        // Exercise & Verify
        $this->expectException(GameException::class);

        $this->middleware->handle($request, $next);
    }

    public function test_handle_コインクエストのcleanupはスルー()
    {
        // Setup
        $user = $this->createUsrUser();
        $now = $this->fixTime('2025-06-13 12:00:00');

        // ステージメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::ENHANCE_QUEST->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // 経験値クエスト（コイン獲得以外）のステージとクエストを作成
        $mstQuest = MstQuest::factory()->create([
            'id' => 'quest_exp_001',
            'quest_type' => QuestType::ENHANCE->value,
        ]);

        $mstStage = MstStage::factory()->create([
            'id' => 'stage_exp_001',
            'mst_quest_id' => 'quest_exp_001',
        ]);

        \App\Domain\Stage\Models\UsrStageSession::factory()->create([
            'usr_user_id' => $user->getId(),
            'mst_stage_id' => 'stage_exp_001',
        ]);

        $request = Request::create('/api/stage/cleanup', 'POST', [
            'mstStageId' => 'stage_exp_001'
        ]);

        // ユーザーを認証済みとして設定
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $next = function ($req) {
            return new Response('cleanup success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('cleanup success', $response->getContent());
    }

    public function test_handle_pvpのcleanupはスルー()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // 個別ガチャメンテナンス中
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::PVP->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        $request = Request::create('/api/pvp/cleanup', 'POST', []);

        $next = function ($req) {
            return new Response('cleanup success', 200);
        };

        // Exercise
        $response = $this->middleware->handle($request, $next);

        // Verify
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('cleanup success', $response->getContent());
    }

    /**
     * v1.1.0時の修正確認用テスト。後のバージョンでは削除して、ショップ系のメンテナンス設定を復活させる予定。
     */
    public function test_handle_shop系メンテナンス設定は無視されて通常動作()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');

        // shop系のメンテナンス設定を作成（全てテスト時間内でアクティブ）
        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::SHOP_ITEM->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::SHOP_PASS->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        MngContentClose::factory()->create([
            'content_type' => ContentMaintenanceType::SHOP_PACK->value,
            'content_id' => null,
            'start_at' => $now->subHours(1)->toDateTimeString(),
            'end_at' => $now->addHours(1)->toDateTimeString(),
            'is_valid' => true,
        ]);

        // shop系APIのリクエストを作成
        $requestShopItem = Request::create('/api/shop/trade_shop_item', 'POST', [
            'shopItemId' => 'shop_item_001'
        ]);

        $requestShopPurchase = Request::create('/api/shop/purchase', 'POST', [
            'shopItemId' => 'shop_item_002'
        ]);

        $requestShopPass = Request::create('/api/shop/purchase_pass', 'POST', [
            'shopPassId' => 'shop_pass_001'
        ]);

        $requestShopPack = Request::create('/api/shop/trade_pack', 'POST', [
            'shopPackId' => 'shop_pack_001'
        ]);

        $next = function ($req) {
            return new Response('success', 200);
        };

        // Exercise & Verify - shop系は全て通常通り動作する
        $responseTradeShopItem = $this->middleware->handle($requestShopItem, $next);
        $this->assertEquals(200, $responseTradeShopItem->getStatusCode());
        $this->assertEquals('success', $responseTradeShopItem->getContent());

        $responseShopPurchase = $this->middleware->handle($requestShopPurchase, $next);
        $this->assertEquals(200, $responseShopPurchase->getStatusCode());
        $this->assertEquals('success', $responseShopPurchase->getContent());

        $responseShopPass = $this->middleware->handle($requestShopPass, $next);
        $this->assertEquals(200, $responseShopPass->getStatusCode());
        $this->assertEquals('success', $responseShopPass->getContent());

        $responseShopPack = $this->middleware->handle($requestShopPack, $next);
        $this->assertEquals(200, $responseShopPack->getStatusCode());
        $this->assertEquals('success', $responseShopPack->getContent());
    }
}
