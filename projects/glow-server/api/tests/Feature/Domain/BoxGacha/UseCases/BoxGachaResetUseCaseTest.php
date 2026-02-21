<?php

namespace Tests\Feature\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\UseCases\BoxGachaResetUseCase;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstBoxGacha;
use App\Domain\Resource\Mst\Models\MstBoxGachaGroup;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstItem;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class BoxGachaResetUseCaseTest extends TestCase
{
    private BoxGachaResetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->app->make(BoxGachaResetUseCase::class);
    }

    // ========================================
    // 正常系テスト
    // ========================================

    public function test_exec_新規ユーザーのリセットが成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_reset_new';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, maxBoxLevel: 3);

        // Exercise - 新規ユーザーは箱レベル1からリセット
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId, 1);

        // Verify - 戻り値
        $this->assertNotNull($result);
        $this->assertEquals(2, $result->usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(1, $result->usrBoxGacha->getResetCount());
        $this->assertEquals(0, $result->usrBoxGacha->getDrawCount());

        // Verify - DB保存
        $this->assertDatabaseHas('usr_box_gachas', [
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 2,
            'reset_count' => 1,
            'draw_count' => 0,
        ]);

        // Verify - ログが記録された
        $this->saveAllLogModel();
        $this->assertDatabaseHas('log_box_gacha_actions', [
            'usr_user_id' => $usrUser->getId(),
            'log_type' => 'Reset',
            'mst_box_gacha_id' => $mstBoxGachaId,
            'total_draw_count' => null,
        ]);
    }

    public function test_exec_既存ユーザーの抽選途中リセットが成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_reset_existing';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, maxBoxLevel: 3);

        // 既存ユーザーデータ（抽選途中）
        UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 30,
            'draw_count' => 30,
            'draw_prizes' => json_encode(['prize_1_lv1' => 20, 'prize_2_lv1' => 10]),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId, 1);

        // Verify - リセット後の状態
        $this->assertEquals(2, $result->usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(1, $result->usrBoxGacha->getResetCount());
        $this->assertEquals(0, $result->usrBoxGacha->getDrawCount());
        $this->assertEquals(30, $result->usrBoxGacha->getTotalDrawCount()); // 累計は維持

        // Verify - DB保存
        $saved = UsrBoxGacha::where('usr_user_id', $usrUser->getId())
            ->where('mst_box_gacha_id', $mstBoxGachaId)
            ->first();
        $this->assertEquals(2, $saved->current_box_level);
        $this->assertEquals(1, $saved->reset_count);
        $this->assertEquals(0, $saved->draw_count);
        $this->assertEquals(30, $saved->total_draw_count);
        $this->assertEquals(0, $saved->getDrawPrizes()->sum());
    }

    // ========================================
    // 異常系テスト
    // ========================================

    public function test_exec_期間外の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_reset_period_error';

        // イベント期間外（2025年1月1日）
        $this->fixTime('2025-01-01 00:00:00');
        $this->createMstTestData($mstBoxGachaId, maxBoxLevel: 3);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_PERIOD_OUTSIDE);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 1);
    }

    public function test_exec_箱レベル不一致の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_reset_level_mismatch';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId, maxBoxLevel: 3);

        // 既存ユーザーデータ（箱レベル2）
        UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 2,
            'reset_count' => 1,
            'total_draw_count' => 30,
            'draw_count' => 30,
            'draw_prizes' => json_encode(['prize_1_lv2_' . $mstBoxGachaId => 20]),
        ]);

        // Exercise & Verify - 箱レベル1を指定（実際は2）
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_BOX_LEVEL_MISMATCH);

        $this->useCase->exec($currentUser, $mstBoxGachaId, 1);
    }

    // ========================================
    // ヘルパーメソッド
    // ========================================

    private function createMstTestData(
        string $mstBoxGachaId,
        int $maxBoxLevel = 1,
        string $loopType = 'All',
    ): void {
        $mstEventId = $mstBoxGachaId . '_event';
        $costItemId = $mstBoxGachaId . '_cost';

        MstItem::factory()->create(['id' => $costItemId]);
        MstItem::factory()->create(['id' => 'reward_item_1']);

        MstEvent::factory()->create([
            'id' => $mstEventId,
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-12-31 23:59:59',
        ]);

        MstBoxGacha::factory()->create([
            'id' => $mstBoxGachaId,
            'mst_event_id' => $mstEventId,
            'loop_type' => $loopType,
            'cost_id' => $costItemId,
            'cost_num' => 1,
        ]);

        for ($level = 1; $level <= $maxBoxLevel; $level++) {
            $groupId = $mstBoxGachaId . '_group_' . $level;

            MstBoxGachaGroup::factory()->create([
                'id' => $groupId,
                'mst_box_gacha_id' => $mstBoxGachaId,
                'box_level' => $level,
            ]);

            MstBoxGachaPrize::factory()->create([
                'id' => 'prize_1_lv' . $level . '_' . $mstBoxGachaId,
                'mst_box_gacha_group_id' => $groupId,
                'is_pickup' => false,
                'resource_type' => 'Item',
                'resource_id' => 'reward_item_1',
                'resource_amount' => 10,
                'stock' => 50,
            ]);
        }
    }
}
