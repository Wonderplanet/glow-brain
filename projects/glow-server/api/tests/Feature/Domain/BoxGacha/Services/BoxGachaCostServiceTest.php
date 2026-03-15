<?php

namespace Tests\Feature\Domain\BoxGacha\Services;

use App\Domain\BoxGacha\Services\BoxGachaCostService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity;
use App\Domain\Resource\Mst\Models\MstItem;
use Tests\TestCase;

class BoxGachaCostServiceTest extends TestCase
{
    private BoxGachaCostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(BoxGachaCostService::class);
    }

    // ========================================
    // calculateTotalCost
    // ========================================

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function calculateTotalCostDataProvider(): array
    {
        // [costNum, drawCount, expectedTotalCost]
        return [
            '複数回抽選: 5 * 10 = 50' => [5, 10, 50],
            '1回抽選: コスト単価と同じ' => [3, 1, 3],
            '大量抽選: 2 * 100 = 200' => [2, 100, 200],
        ];
    }

    /**
     * @dataProvider calculateTotalCostDataProvider
     */
    public function test_calculateTotalCost(int $costNum, int $drawCount, int $expectedTotalCost): void
    {
        // Setup
        $mstBoxGacha = $this->createMstBoxGachaEntity(costNum: $costNum);

        // Exercise
        $result = $this->service->calculateTotalCost($mstBoxGacha, $drawCount);

        // Verify
        $this->assertEquals($expectedTotalCost, $result);
    }

    // ========================================
    // validateCost
    // ========================================

    /**
     * @return array<string, array{int, int, int, int|null}>
     */
    public static function validateCostDataProvider(): array
    {
        // [itemAmount, costNum, drawCount, expectedErrorCode (null=正常)]
        return [
            '正常: 十分なアイテムがある' => [100, 5, 10, null], // 必要50、所持100
            '正常: ちょうど必要量と同じ' => [50, 5, 10, null],  // 必要50、所持50
            'エラー: アイテム不足' => [30, 5, 10, ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH], // 必要50、所持30
            'エラー: 1個不足' => [49, 5, 10, ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH], // 必要50、所持49
        ];
    }

    /**
     * @dataProvider validateCostDataProvider
     */
    public function test_validateCost(
        int $itemAmount,
        int $costNum,
        int $drawCount,
        ?int $expectedErrorCode,
    ): void {
        // Setup
        $usrUser = $this->createUsrUser();
        $costItemId = 'cost_item_validate_' . $itemAmount;
        MstItem::factory()->create(['id' => $costItemId]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $costItemId,
            'amount' => $itemAmount,
        ]);

        $mstBoxGacha = $this->createMstBoxGachaEntity(costId: $costItemId, costNum: $costNum);

        // Exercise & Verify
        if ($expectedErrorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($expectedErrorCode);
        }

        $this->service->validateCost($usrUser->getId(), $mstBoxGacha, $drawCount);

        if ($expectedErrorCode === null) {
            $this->assertTrue(true);
        }
    }

    // ========================================
    // consumeCost
    // ========================================

    /**
     * @return array<string, array{int, int, int, int}>
     */
    public static function consumeCostDataProvider(): array
    {
        // [initialAmount, costNum, drawCount, expectedRemainingAmount]
        return [
            '複数回抽選: 100 - 50 = 50' => [100, 5, 10, 50],
            '1回抽選: 10 - 3 = 7' => [10, 3, 1, 7],
            '全消費: 50 - 50 = 0' => [50, 5, 10, 0],
        ];
    }

    /**
     * @dataProvider consumeCostDataProvider
     */
    public function test_consumeCost(
        int $initialAmount,
        int $costNum,
        int $drawCount,
        int $expectedRemainingAmount,
    ): void {
        // Setup
        $usrUser = $this->createUsrUser();
        $costItemId = 'cost_item_consume_' . $initialAmount . '_' . $costNum;
        MstItem::factory()->create(['id' => $costItemId]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $costItemId,
            'amount' => $initialAmount,
        ]);

        $mstBoxGacha = $this->createMstBoxGachaEntity(costId: $costItemId, costNum: $costNum);

        // Exercise
        $this->service->consumeCost($usrUser->getId(), $mstBoxGacha, $drawCount, 1);
        $this->saveAll();

        // Verify
        $usrItem = UsrItem::where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $costItemId)
            ->first();
        $this->assertEquals($expectedRemainingAmount, $usrItem->amount);
    }

    // ========================================
    // ヘルパーメソッド
    // ========================================

    private function createMstBoxGachaEntity(
        string $costId = 'cost_item',
        int $costNum = 1,
    ): MstBoxGachaEntity {
        return new MstBoxGachaEntity(
            'test_box_gacha',
            'event_1',
            $costId,
            $costNum,
            'All',
        );
    }
}
