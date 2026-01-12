<?php

namespace Tests\Feature\Domain\BoxGacha\UseCases;

use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\UseCases\BoxGachaInfoUseCase;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstBoxGacha;
use App\Domain\Resource\Mst\Models\MstBoxGachaGroup;
use App\Domain\Resource\Mst\Models\MstBoxGachaPrize;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstItem;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class BoxGachaInfoUseCaseTest extends TestCase
{
    private BoxGachaInfoUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = $this->app->make(BoxGachaInfoUseCase::class);
    }

    // ========================================
    // 正常系テスト
    // ========================================

    public function test_exec_新規ユーザーの情報取得が成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_info_new';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId);

        // Verify - 初期状態のUsrBoxGachaが返される
        $this->assertNotNull($result);
        $this->assertEquals(1, $result->usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(0, $result->usrBoxGacha->getResetCount());
        $this->assertEquals(0, $result->usrBoxGacha->getTotalDrawCount());
        $this->assertEquals(0, $result->usrBoxGacha->getDrawCount());

        // Verify - DBには保存されていない（getOrMakeはDB保存しない）
        $this->assertDatabaseMissing('usr_box_gachas', [
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
        ]);
    }

    public function test_exec_既存ユーザーの情報取得が成功する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_info_existing';

        $this->fixTime('2024-06-15 12:00:00');
        $this->createMstTestData($mstBoxGachaId);

        // 既存ユーザーデータ
        UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 2,
            'reset_count' => 1,
            'total_draw_count' => 60,
            'draw_count' => 30,
            'draw_prizes' => json_encode(['prize_1' => 20, 'prize_2' => 10]),
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $mstBoxGachaId);

        // Verify - 既存データが返される
        $this->assertNotNull($result);
        $this->assertEquals(2, $result->usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(1, $result->usrBoxGacha->getResetCount());
        $this->assertEquals(60, $result->usrBoxGacha->getTotalDrawCount());
        $this->assertEquals(30, $result->usrBoxGacha->getDrawCount());
    }

    // ========================================
    // 異常系テスト
    // ========================================

    public function test_exec_期間外の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $currentUser = new CurrentUser($usrUser->getId());
        $mstBoxGachaId = 'box_gacha_info_period_error';

        // イベント期間外（2025年1月1日）
        $this->fixTime('2025-01-01 00:00:00');
        $this->createMstTestData($mstBoxGachaId);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_PERIOD_OUTSIDE);

        $this->useCase->exec($currentUser, $mstBoxGachaId);
    }

    // ========================================
    // ヘルパーメソッド
    // ========================================

    private function createMstTestData(string $mstBoxGachaId): void
    {
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
            'loop_type' => 'All',
            'cost_id' => $costItemId,
            'cost_num' => 1,
        ]);

        $groupId = $mstBoxGachaId . '_group_1';

        MstBoxGachaGroup::factory()->create([
            'id' => $groupId,
            'mst_box_gacha_id' => $mstBoxGachaId,
            'box_level' => 1,
        ]);

        MstBoxGachaPrize::factory()->create([
            'id' => 'prize_1_' . $mstBoxGachaId,
            'mst_box_gacha_group_id' => $groupId,
            'is_pickup' => false,
            'resource_type' => 'Item',
            'resource_id' => 'reward_item_1',
            'resource_amount' => 10,
            'stock' => 50,
        ]);
    }
}
