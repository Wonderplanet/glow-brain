<?php

namespace Tests\Feature\Domain\BoxGacha\Services;

use App\Domain\BoxGacha\Enums\BoxGachaLoopType;
use App\Domain\BoxGacha\Models\UsrBoxGacha;
use App\Domain\BoxGacha\Services\BoxGachaService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity;
use App\Domain\Resource\Mst\Entities\MstBoxGachaPrizeEntity;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstEvent;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BoxGachaServiceTest extends TestCase
{
    private BoxGachaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(BoxGachaService::class);
    }

    // ========================================
    // validateBoxGachaPeriod
    // ========================================

    /**
     * @return array<string, array{string, bool}>
     */
    public static function validateBoxGachaPeriodDataProvider(): array
    {
        // [now, shouldError] ※イベント期間: 2024-01-01 00:00:00 ~ 2024-12-31 23:59:59
        return [
            '正常: 期間内' => ['2024-06-15 12:00:00', false],
            'エラー: 開始前' => ['2023-12-31 23:59:59', true],
            'エラー: 終了後' => ['2025-01-01 00:00:00', true],
        ];
    }

    /**
     * @dataProvider validateBoxGachaPeriodDataProvider
     */
    public function test_validateBoxGachaPeriod(string $now, bool $shouldError): void
    {
        // Setup
        $nowTime = $this->fixTime($now);
        $mstEvent = MstEvent::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-12-31 23:59:59',
        ]);
        $mstBoxGacha = $this->createMstBoxGachaEntity($mstEvent->id);

        // Exercise & Verify
        if ($shouldError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::BOX_GACHA_PERIOD_OUTSIDE);
        }

        $this->service->validateBoxGachaPeriod($mstBoxGacha, $nowTime);

        if (!$shouldError) {
            $this->assertTrue(true);
        }
    }

    // ========================================
    // calculateNextBoxLevel
    // ========================================

    /**
     * calculateNextBoxLevelのテストデータ
     *
     * @return array<string, array{int, array<int>, BoxGachaLoopType, int}>
     */
    public static function calculateNextBoxLevelDataProvider(): array
    {
        // [beforeResetCount, boxLevels, loopType, expectedNextBoxLevel]
        return [
            // All: 全BOXレベルをループ
            'All: リセット0回目→箱レベル2' => [0, [1, 2, 3], BoxGachaLoopType::ALL, 2],
            'All: リセット1回目→箱レベル3' => [1, [1, 2, 3], BoxGachaLoopType::ALL, 3],
            'All: リセット2回目→箱レベル1（ループ）' => [2, [1, 2, 3], BoxGachaLoopType::ALL, 1],
            'All: リセット3回目→箱レベル2（ループ2周目）' => [3, [1, 2, 3], BoxGachaLoopType::ALL, 2],
            'All: リセット5回目→箱レベル1（ループ2周目）' => [5, [1, 2, 3], BoxGachaLoopType::ALL, 1],

            // Last: 最後のBOXレベルで固定
            'Last: リセット0回目→箱レベル2' => [0, [1, 2, 3], BoxGachaLoopType::LAST, 2],
            'Last: リセット1回目→箱レベル3' => [1, [1, 2, 3], BoxGachaLoopType::LAST, 3],
            'Last: リセット2回目→箱レベル3（最後で固定）' => [2, [1, 2, 3], BoxGachaLoopType::LAST, 3],
            'Last: リセット5回目→箱レベル3（最後で固定が続く）' => [5, [1, 2, 3], BoxGachaLoopType::LAST, 3],

            // First: 1番目のBOXレベルで固定
            'First: リセット0回目→箱レベル2' => [0, [1, 2, 3], BoxGachaLoopType::FIRST, 2],
            'First: リセット1回目→箱レベル3' => [1, [1, 2, 3], BoxGachaLoopType::FIRST, 3],
            'First: リセット2回目→箱レベル1（最初に戻る）' => [2, [1, 2, 3], BoxGachaLoopType::FIRST, 1],
            'First: リセット5回目→箱レベル1（最初で固定が続く）' => [5, [1, 2, 3], BoxGachaLoopType::FIRST, 1],

            // boxLevelが連番でない場合（例: 1, 3, 5）
            'All: 非連番boxLevel リセット0回目→箱レベル3' => [0, [1, 3, 5], BoxGachaLoopType::ALL, 3],
            'All: 非連番boxLevel リセット1回目→箱レベル5' => [1, [1, 3, 5], BoxGachaLoopType::ALL, 5],
            'All: 非連番boxLevel リセット2回目→箱レベル1（ループ）' => [2, [1, 3, 5], BoxGachaLoopType::ALL, 1],

            // boxLevelが1つだけの場合
            'All: 1箱のみ リセット0回目→箱レベル1（ループ）' => [0, [1], BoxGachaLoopType::ALL, 1],
            'Last: 1箱のみ リセット0回目→箱レベル1' => [0, [1], BoxGachaLoopType::LAST, 1],
            'First: 1箱のみ リセット0回目→箱レベル1' => [0, [1], BoxGachaLoopType::FIRST, 1],

            // boxLevelが2つの場合
            'All: 2箱 リセット0回目→箱レベル2' => [0, [1, 2], BoxGachaLoopType::ALL, 2],
            'All: 2箱 リセット1回目→箱レベル1（ループ）' => [1, [1, 2], BoxGachaLoopType::ALL, 1],
            'All: 2箱 リセット2回目→箱レベル2（ループ）' => [2, [1, 2], BoxGachaLoopType::ALL, 2],
        ];
    }

    /**
     * @dataProvider calculateNextBoxLevelDataProvider
     * @param array<int> $boxLevels
     */
    public function test_calculateNextBoxLevel(
        int $beforeResetCount,
        array $boxLevels,
        BoxGachaLoopType $loopType,
        int $expectedNextBoxLevel,
    ): void {
        // Exercise - privateメソッドを直接テスト
        $result = $this->execPrivateMethod(
            $this->service,
            'calculateNextBoxLevel',
            [$beforeResetCount, $boxLevels, $loopType]
        );

        // Verify
        $this->assertEquals($expectedNextBoxLevel, $result);
    }

    public function test_calculateNextBoxLevel_boxLevelsが空の場合エラーになる(): void
    {
        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        $this->execPrivateMethod(
            $this->service,
            'calculateNextBoxLevel',
            [0, [], BoxGachaLoopType::ALL]
        );
    }

    // ========================================
    // validateDrawCount
    // ========================================

    /**
     * @return array<string, array{int, int, int|null, int|null}>
     */
    public static function validateDrawCountDataProvider(): array
    {
        // [drawCount, remainingStock, maxDrawCountConfig, expectedErrorCode]
        // [抽選回数, 残り在庫, MstConfig設定値（null=未設定）, 期待エラーコード（null=正常）]
        return [
            '正常: 在庫以下' => [5, 10, null, null],
            '正常: デフォルト上限100' => [100, 200, null, null],
            '正常: MstConfig設定値50' => [50, 200, 50, null],
            'エラー: 0以下' => [0, 10, null, ErrorCode::INVALID_PARAMETER],
            'エラー: 在庫超過' => [15, 10, null, ErrorCode::BOX_GACHA_NOT_ENOUGH_STOCK],
            'エラー: デフォルト上限超過' => [101, 200, null, ErrorCode::BOX_GACHA_EXCEED_DRAW_LIMIT],
            'エラー: MstConfig設定値超過' => [51, 200, 50, ErrorCode::BOX_GACHA_EXCEED_DRAW_LIMIT],
        ];
    }

    /**
     * @dataProvider validateDrawCountDataProvider
     */
    public function test_validateDrawCount(
        int $drawCount,
        int $remainingStock,
        ?int $maxDrawCountConfig,
        ?int $expectedErrorCode,
    ): void {
        // Setup - MstConfig設定（必要な場合のみ）
        if ($maxDrawCountConfig !== null) {
            MstConfig::factory()->create([
                'key' => MstConfigConstant::BOX_GACHA_MAX_DRAW_COUNT,
                'value' => (string) $maxDrawCountConfig,
            ]);
            $this->service = $this->app->make(BoxGachaService::class);
        }

        // Exercise & Verify
        if ($expectedErrorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($expectedErrorCode);
        }

        $this->service->validateDrawCount($drawCount, $remainingStock);

        if ($expectedErrorCode === null) {
            $this->assertTrue(true);
        }
    }

    // ========================================
    // validateCurrentBoxLevel
    // ========================================

    public function test_validateCurrentBoxLevel_一致する場合エラーにならない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'current_box_level' => 2,
        ]);

        // Exercise & Verify
        $this->service->validateCurrentBoxLevel(2, $usrBoxGacha);
        $this->assertTrue(true);
    }

    public function test_validateCurrentBoxLevel_不一致の場合エラーになる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'current_box_level' => 2,
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BOX_GACHA_BOX_LEVEL_MISMATCH);
        $this->service->validateCurrentBoxLevel(1, $usrBoxGacha);
    }

    // ========================================
    // calculateRemainingStock
    // ========================================

    public function test_calculateRemainingStock_抽選済みがない場合は全在庫を返す(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 10],
            ['id' => 'p2', 'stock' => 20],
        ]);
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'draw_prizes' => json_encode([]),
        ]);

        // Exercise
        $result = $this->service->calculateRemainingStock($prizes, $usrBoxGacha);

        // Verify
        $this->assertEquals(30, $result);
    }

    public function test_calculateRemainingStock_抽選済みがある場合は差し引いた在庫を返す(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 10],
            ['id' => 'p2', 'stock' => 20],
        ]);
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'draw_prizes' => json_encode(['p1' => 5, 'p2' => 10]),
        ]);

        // Exercise
        $result = $this->service->calculateRemainingStock($prizes, $usrBoxGacha);

        // Verify - (10-5) + (20-10) = 15
        $this->assertEquals(15, $result);
    }

    // ========================================
    // getAvailablePrizes
    // ========================================

    public function test_getAvailablePrizes_在庫がある賞品のみ返す(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 10],
            ['id' => 'p2', 'stock' => 5],
        ]);
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'draw_prizes' => json_encode(['p1' => 10]), // p1は在庫切れ
        ]);

        // Exercise
        $result = $this->service->getAvailablePrizes($prizes, $usrBoxGacha);

        // Verify
        $this->assertCount(1, $result);
        $this->assertEquals('p2', $result->first()->getId());
    }

    public function test_getAvailablePrizes_全賞品に在庫がある場合は全て返す(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 10],
            ['id' => 'p2', 'stock' => 5],
            ['id' => 'p3', 'stock' => 3],
        ]);
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'draw_prizes' => json_encode([]), // 抽選なし
        ]);

        // Exercise
        $result = $this->service->getAvailablePrizes($prizes, $usrBoxGacha);

        // Verify
        $this->assertCount(3, $result);
    }

    public function test_getAvailablePrizes_全賞品が在庫切れの場合は空を返す(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 10],
            ['id' => 'p2', 'stock' => 5],
        ]);
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'draw_prizes' => json_encode(['p1' => 10, 'p2' => 5]), // 全て在庫切れ
        ]);

        // Exercise
        $result = $this->service->getAvailablePrizes($prizes, $usrBoxGacha);

        // Verify
        $this->assertCount(0, $result);
    }

    // ========================================
    // calculateDrawResult
    // ========================================

    public function test_calculateDrawResult_指定回数分の報酬が返される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha',
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ]);
        $prizes = $this->createPrizeCollection([
            ['id' => 'p1', 'stock' => 50, 'type' => 'Item', 'resource_id' => 'item_1', 'amount' => 1],
            ['id' => 'p2', 'stock' => 50, 'type' => 'Item', 'resource_id' => 'item_2', 'amount' => 2],
        ]);
        $drawCount = 10;

        // Exercise
        $result = $this->execPrivateMethod($this->service, 'calculateDrawResult', [$prizes, $usrBoxGacha, $drawCount, 'test_gacha']);

        // Verify - 報酬の数
        $this->assertCount($drawCount, $result->getRewards());

        // Verify - UsrBoxGachaモデルが更新されている
        $this->assertEquals($drawCount, $usrBoxGacha->getTotalDrawCount());
        $this->assertEquals($drawCount, $usrBoxGacha->getDrawCount());
        $this->assertEquals($drawCount, $usrBoxGacha->getDrawPrizes()->sum());
        $this->assertEquals(1, $usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(0, $usrBoxGacha->getResetCount());
    }

    public function test_calculateDrawResult_在庫1の賞品も抽選される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha_rare',
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ]);
        $prizes = $this->createPrizeCollection([
            ['id' => 'rare', 'stock' => 1, 'type' => 'Item', 'resource_id' => 'item_rare', 'amount' => 1],
            ['id' => 'common', 'stock' => 9, 'type' => 'Item', 'resource_id' => 'item_common', 'amount' => 1],
        ]);

        // Exercise - 全在庫を引き切る
        $result = $this->execPrivateMethod($this->service, 'calculateDrawResult', [$prizes, $usrBoxGacha, 10, 'test_gacha_rare']);

        // Verify - rareは必ず1回抽選される
        $rareCount = $result->getRewards()->filter(
            fn($r) => $r->getMstBoxGachaPrizeId() === 'rare'
        )->count();
        $this->assertEquals(1, $rareCount);
    }

    public function test_calculateDrawResult_在庫切れの賞品は抽選されない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha_out_of_stock',
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 5,
            'draw_count' => 5,
            'draw_prizes' => json_encode(['prize_a' => 5]), // prize_aを5回引いた（在庫切れ）
        ]);
        $prizes = $this->createPrizeCollection([
            ['id' => 'prize_a', 'stock' => 5, 'type' => 'Item', 'resource_id' => 'item_a', 'amount' => 1],
            ['id' => 'prize_b', 'stock' => 5, 'type' => 'Item', 'resource_id' => 'item_b', 'amount' => 1],
        ]);

        // Exercise - 5回抽選（prize_bしか残っていない）
        $availablePrizes = $this->service->getAvailablePrizes($prizes, $usrBoxGacha);
        $result = $this->execPrivateMethod($this->service, 'calculateDrawResult', [$availablePrizes, $usrBoxGacha, 5, 'test_gacha_out_of_stock']);

        // Verify - 全てprize_b（item_b）が出る
        $rewards = $result->getRewards();
        $this->assertCount(5, $rewards);
        foreach ($rewards as $reward) {
            $this->assertEquals('prize_b', $reward->getMstBoxGachaPrizeId());
            $this->assertEquals('item_b', $reward->getResourceId());
        }

        // Verify - draw_prizesにprize_bが5回追加されている
        $this->assertEquals(5, $usrBoxGacha->getDrawnCountByPrizeId('prize_a')); // 元の5回
        $this->assertEquals(5, $usrBoxGacha->getDrawnCountByPrizeId('prize_b')); // 新しく5回
    }

    public function test_calculateDrawResult_prizeLogsに賞品別の抽選回数が記録される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => 'test_gacha_logs',
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ]);
        $prizes = $this->createPrizeCollection([
            ['id' => 'prize_a', 'stock' => 3, 'type' => 'Item', 'resource_id' => 'item_a', 'amount' => 1],
            ['id' => 'prize_b', 'stock' => 7, 'type' => 'Item', 'resource_id' => 'item_b', 'amount' => 1],
        ]);

        // Exercise - 全在庫を引き切る
        $result = $this->execPrivateMethod($this->service, 'calculateDrawResult', [$prizes, $usrBoxGacha, 10, 'test_gacha_logs']);

        // Verify - prizeLogsの検証
        $prizeLogs = $result->getPrizeLogs();

        // 各賞品のログが存在する
        $this->assertCount(2, $prizeLogs);

        // 合計抽選回数がdrawCountと一致
        $totalLogCount = $prizeLogs->sum(fn($log) => $log->getDrawCount());
        $this->assertEquals(10, $totalLogCount);

        // 各ログの抽選回数がrewardsと一致することを確認
        foreach ($prizeLogs as $log) {
            $rewardCount = $result->getRewards()->filter(
                fn($r) => $r->getMstBoxGachaPrizeId() === $log->getMstBoxGachaPrizeId()
            )->count();
            $this->assertEquals($rewardCount, $log->getDrawCount());
        }
    }

    // ========================================
    // resetBox（統合テスト）
    // ========================================

    public function test_resetBox_次の箱レベルに進む(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstBoxGachaId = 'box_gacha_reset_integration';

        // マスターデータ作成（3箱構成）
        $this->createMstBoxGachaWithGroups($mstBoxGachaId, 'All', 3);

        // ユーザーデータ作成（箱レベル1、リセット0回）
        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 0,
            'draw_count' => 0,
            'draw_prizes' => json_encode([]),
        ]);

        $mstBoxGacha = $this->createMstBoxGachaEntity(
            id: $mstBoxGachaId,
            loopType: 'All'
        );

        // Exercise
        $this->service->resetBox($usrBoxGacha, $mstBoxGacha);

        // Verify - 箱レベル2に進む
        $this->assertEquals(2, $usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(1, $usrBoxGacha->getResetCount());
        $this->assertEquals(0, $usrBoxGacha->getDrawCount());
    }

    public function test_resetBox_抽選途中でもリセットできる(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstBoxGachaId = 'box_gacha_reset_midway';

        $this->createMstBoxGachaWithGroups($mstBoxGachaId, 'All', 2);

        $usrBoxGacha = UsrBoxGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_box_gacha_id' => $mstBoxGachaId,
            'current_box_level' => 1,
            'reset_count' => 0,
            'total_draw_count' => 30,
            'draw_count' => 30,
            'draw_prizes' => json_encode(['prize_1' => 20, 'prize_2' => 10]),
        ]);

        $mstBoxGacha = $this->createMstBoxGachaEntity(id: $mstBoxGachaId);

        // Exercise
        $this->service->resetBox($usrBoxGacha, $mstBoxGacha);

        // Verify - 抽選情報がリセットされる
        $this->assertEquals(2, $usrBoxGacha->getCurrentBoxLevel());
        $this->assertEquals(1, $usrBoxGacha->getResetCount());
        $this->assertEquals(0, $usrBoxGacha->getDrawCount());
        $this->assertEquals(30, $usrBoxGacha->getTotalDrawCount()); // 累計は維持
        $this->assertEquals(0, $usrBoxGacha->getDrawPrizes()->sum());
    }

    // ========================================
    // ヘルパーメソッド
    // ========================================

    private function createMstBoxGachaEntity(
        string $mstEventId = 'event_1',
        string $id = 'test_box_gacha',
        string $loopType = 'All',
    ): MstBoxGachaEntity {
        return new MstBoxGachaEntity(
            $id,
            $mstEventId,
            'cost_item',
            1,
            $loopType,
        );
    }

    /**
     * BOXガチャのマスターデータとグループを作成
     */
    private function createMstBoxGachaWithGroups(
        string $mstBoxGachaId,
        string $loopType,
        int $maxBoxLevel,
    ): void {
        $mstEventId = $mstBoxGachaId . '_event';

        MstEvent::factory()->create([
            'id' => $mstEventId,
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-12-31 23:59:59',
        ]);

        \App\Domain\Resource\Mst\Models\MstBoxGacha::factory()->create([
            'id' => $mstBoxGachaId,
            'mst_event_id' => $mstEventId,
            'loop_type' => $loopType,
            'cost_id' => 'cost_item',
            'cost_num' => 1,
        ]);

        for ($level = 1; $level <= $maxBoxLevel; $level++) {
            \App\Domain\Resource\Mst\Models\MstBoxGachaGroup::factory()->create([
                'id' => $mstBoxGachaId . '_group_' . $level,
                'mst_box_gacha_id' => $mstBoxGachaId,
                'box_level' => $level,
            ]);
        }
    }

    private function createPrizeCollection(array $prizesData): Collection
    {
        return collect(array_map(
            fn($data) => new MstBoxGachaPrizeEntity(
                $data['id'],
                'group_1',
                $data['is_pickup'] ?? false,
                $data['type'] ?? 'Item',
                $data['resource_id'] ?? 'item_1',
                $data['amount'] ?? 1,
                $data['stock'],
            ),
            $prizesData
        ));
    }
}
