<?php

namespace Tests\Feature\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Models\LogBoxGachaAction;
use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\UseCases\BoxGachaDrawUseCase;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstBoxGacha;
use App\Domain\Resource\Mst\Models\MstBoxGachaGroup;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class BoxGachaDrawUseCaseTest extends TestCase
{
    private BoxGachaDrawUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->app->make(BoxGachaDrawUseCase::class);
    }

    // ========================================
    // 正常系テスト
    // ========================================

    public function test_exec_新規ユーザーの抽選が成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_new_user';
        $costItemId = 'cost_item_new_user';
        $drawCount = 3;
        $platform = 1;

        $this->fixTime('2024-06-15 12:00:00');

        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 100);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId, $drawCount, 1, $platform);

        // Verify - 戻り値
        $this->assertNotNull($result);
        $this->assertCount($drawCount, $result->boxGachaRewards);
        $this->assertEquals($drawCount, $result->usrBoxGacha->getTotalDrawCount());
        $this->assertEquals($drawCount, $result->usrBoxGacha->getDrawCount());

        // Verify - DBに保存されたUsrBoxGacha
        $savedUsrBoxGacha = UsrBoxGacha::where('usr_user_id', $usrUser->getId())
            ->where('mst_box_gacha_id', $mstBoxGachaId)
            ->first();
        $this->assertNotNull($savedUsrBoxGacha);
        $this->assertEquals(1, $savedUsrBoxGacha->current_box_level);
        $this->assertEquals(0, $savedUsrBoxGacha->reset_count);
        $this->assertEquals($drawCount, $savedUsrBoxGacha->total_draw_count);
        $this->assertEquals($drawCount, $savedUsrBoxGacha->draw_count);
        $this->assertEquals($drawCount, $savedUsrBoxGacha->getDrawPrizes()->sum());

        // Verify - コストが消費された
        $usrItem = UsrItem::where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $costItemId)
            ->first();
        $this->assertEquals(100 - $drawCount, $usrItem->amount);

        // Verify - ログが記録された
        $this->saveAllLogModel();
        $log = LogBoxGachaAction::where('usr_user_id', $usrUser->getId())
            ->where('mst_box_gacha_id', $mstBoxGachaId)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Draw', $log->log_type);
        $this->assertEquals($drawCount, $log->total_draw_count);

        // draw_prizesの検証（形式: [{"drawCount": N, "mstBoxGachaPrizeId": "xxx"}, ...]）
        $logDrawPrizes = $log->getDrawPrizes();
        $validPrizeIds = ['prize_1_lv1', 'prize_2_lv1'];
        $totalLogDrawCount = $logDrawPrizes->sum(fn($prize) => $prize['drawCount']);

        $this->assertEquals($drawCount, $totalLogDrawCount, 'ログのdraw_prizes合計が抽選回数と一致しない');
        $logDrawPrizes->each(function ($prize) use ($validPrizeIds) {
            $this->assertContains($prize['mstBoxGachaPrizeId'], $validPrizeIds, 'ログに不正な賞品ID');
        });
    }

    public function test_exec_既存ユーザーの追加抽選が成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_existing';
        $costItemId = 'cost_item_existing';
        $drawCount = 5;
        $platform = 1;
        $initialTotalDrawCount = 10;

        $this->fixTime('2024-06-15 12:00:00');

        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 100);

        // 既存ユーザーデータ作成
        UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => $initialTotalDrawCount,
            'draw_count' => $initialTotalDrawCount,
            'draw_prizes' => json_encode(['prize_1' => 5, 'prize_2' => 5]),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId, $drawCount, 1, $platform);

        // Verify - 抽選回数が増加している
        $this->assertEquals($initialTotalDrawCount + $drawCount, $result->usrBoxGacha->getTotalDrawCount());
        $this->assertEquals($initialTotalDrawCount + $drawCount, $result->usrBoxGacha->getDrawCount());

        // Verify - DBに反映されている
        $savedUsrBoxGacha = UsrBoxGacha::where('usr_user_id', $usrUser->getId())
            ->where('mst_box_gacha_id', $mstBoxGachaId)
            ->first();
        $this->assertEquals($initialTotalDrawCount + $drawCount, $savedUsrBoxGacha->total_draw_count);
    }

    public function test_exec_在庫全てを引き切れる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_full_stock';
        $costItemId = 'cost_item_full_stock';
        $totalStock = 60; // prize_1(50) + prize_2(10)
        $platform = 1;

        $this->fixTime('2024-06-15 12:00:00');

        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 1000);

        // Exercise - 全在庫を引き切る
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId, $totalStock, 1, $platform);

        // Verify
        $this->assertCount($totalStock, $result->boxGachaRewards);
        $this->assertEquals($totalStock, $result->usrBoxGacha->getTotalDrawCount());
        $this->assertEquals($totalStock, $result->usrBoxGacha->getDrawPrizes()->sum());
    }

    // ========================================
    // 異常系テスト
    // ========================================

    public function test_exec_期間外の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_period_error';
        $costItemId = 'cost_item_period_error';

        // イベント期間外（2025年1月1日）
        $this->fixTime('2025-01-01 00:00:00');

        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 100);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_PERIOD_OUTSIDE);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 1, 1, 1);
    }

    public function test_exec_箱レベル不一致の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_level_mismatch';
        $costItemId = 'cost_item_level_mismatch';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, $costItemId, maxBoxLevel: 3);
        $this->createUsrItem($usrUser->getId(), $costItemId, 100);

        // 既存ユーザーデータ（箱レベル2）
        UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 2,
            'reset_count' => 1,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ]);

        // Exercise & Verify - 箱レベル1を指定（実際は2）
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_BOX_LEVEL_MISMATCH);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 1, 1, 1);
    }

    public function test_exec_抽選回数が上限を超える場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_exceed_limit';
        $costItemId = 'cost_item_exceed_limit';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 1000);

        // Exercise & Verify - デフォルト上限100を超える101回を指定
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_EXCEED_DRAW_LIMIT);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 101, 1, 1);
    }

    public function test_exec_在庫を超える抽選の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_not_enough_stock';
        $costItemId = 'cost_item_not_enough_stock';
        $totalStock = 60; // prize_1(50) + prize_2(10)

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, $costItemId);
        $this->createUsrItem($usrUser->getId(), $costItemId, 1000);

        // Exercise & Verify - 在庫60を超える61回を指定
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_NOT_ENOUGH_STOCK);

        $this->useCase->exec($currentUser, $mstBoxGachaId, $totalStock + 1, 1, 1);
    }

    public function test_exec_コスト不足の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $this->createUsrUserRelatedData($usrUser->getId());
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_cost_shortage';
        $costItemId = 'cost_item_cost_shortage';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, $costItemId);
        // コスト不足（10回抽選したいが5個しか持っていない）
        $this->createUsrItem($usrUser->getId(), $costItemId, 5);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 10, 1, 1);
    }

    // ========================================
    // ヘルパーメソッド
    // ========================================

    /**
     * テスト用マスターデータを作成
     *
     * @param string $mstBoxGachaId BOXガチャID
     * @param string $costItemId コストアイテムID
     * @param int $maxBoxLevel 最大箱レベル（デフォルト1）
     */
    private function createMstTestData(
        string $mstBoxGachaId,
        string $costItemId,
        int $maxBoxLevel = 1,
    ): void {
        $mstEventId = $mstBoxGachaId . '_event';

        // コストアイテムマスター
        MstItem::factory()->create(['id' => $costItemId]);

        // 報酬アイテムマスター
        MstItem::factory()->create(['id' => 'reward_item_1']);
        MstItem::factory()->create(['id' => 'reward_item_2']);

        // イベントマスター（2024/1/1〜12/31）
        MstEvent::factory()->create([
            'id' => $mstEventId,
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-12-31 23:59:59',
        ]);

        // BOXガチャマスター
        MstBoxGacha::factory()->create([
            'id' => $mstBoxGachaId,
            'mst_event_id' => $mstEventId,
            'loop_type' => 'All',
            'cost_id' => $costItemId,
            'cost_num' => 1,
        ]);

        // 箱レベルごとのグループと賞品を作成
        for ($level = 1; $level <= $maxBoxLevel; $level++) {
            $groupId = $mstBoxGachaId . '_group_' . $level;

            MstBoxGachaGroup::factory()->create([
                'id' => $groupId,
                'mst_box_gacha_id' => $mstBoxGachaId,
                'box_level' => $level,
            ]);

            MstBoxGachaPrize::factory()->create([
                'id' => 'prize_1_lv' . $level,
                'mst_box_gacha_group_id' => $groupId,
                'is_pickup' => false,
                'resource_type' => 'Item',
                'resource_id' => 'reward_item_1',
                'resource_amount' => 10,
                'stock' => 50,
            ]);

            MstBoxGachaPrize::factory()->create([
                'id' => 'prize_2_lv' . $level,
                'mst_box_gacha_group_id' => $groupId,
                'is_pickup' => true,
                'resource_type' => 'Item',
                'resource_id' => 'reward_item_2',
                'resource_amount' => 5,
                'stock' => 10,
            ]);
        }
    }

    private function createUsrItem(string $usrUserId, string $mstItemId, int $amount): void
    {
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $amount,
        ]);
    }

    private function createUsrUserRelatedData(string $usrUserId): void
    {
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUserId]);
    }
}
