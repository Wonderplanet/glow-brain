<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Exchange\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Exchange\Constants\ExchangeConstant;
use App\Domain\Exchange\Enums\ExchangeTradeType;
use App\Domain\Exchange\Models\LogExchangeAction;
use App\Domain\Exchange\Models\UsrExchangeLineup;
use App\Domain\Exchange\Repositories\UsrExchangeLineupRepository;
use App\Domain\Exchange\Services\ExchangeService;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Entities\Rewards\ExchangeTradeReward;
use App\Domain\Resource\Mst\Models\MstExchange;
use App\Domain\Resource\Mst\Models\MstExchangeCost;
use App\Domain\Resource\Mst\Models\MstExchangeLineup;
use App\Domain\Resource\Mst\Models\MstExchangeReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class ExchangeServiceTest extends TestCase
{
    private ExchangeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ExchangeService::class);
        $this->createUsrUser();
        UsrCurrencySummary::factory()->create(['usr_user_id' => $this->usrUserId]);
        $this->createMasterRelease();
    }

    /**
     * 交換所期間のバリデーション
     */
    public static function params_test_validateExchange_交換所期間チェック()
    {
        return [
            '期間前' => [
                'now' => '2024-01-01 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => '2024-01-10 00:00:00',
                'errorCode' => ErrorCode::EXCHANGE_NOT_TRADE_PERIOD,
            ],
            '期間開始時刻' => [
                'now' => '2024-01-02 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => '2024-01-10 00:00:00',
                'errorCode' => null,
            ],
            '期間中' => [
                'now' => '2024-01-05 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => '2024-01-10 00:00:00',
                'errorCode' => null,
            ],
            '期間終了時刻' => [
                'now' => '2024-01-10 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => '2024-01-10 00:00:00',
                'errorCode' => null,
            ],
            '期間後' => [
                'now' => '2024-01-10 00:00:01',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => '2024-01-10 00:00:00',
                'errorCode' => ErrorCode::EXCHANGE_NOT_TRADE_PERIOD,
            ],
            '無期限_開始日時より前' => [
                'now' => '2024-01-01 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => null,
                'errorCode' => ErrorCode::EXCHANGE_NOT_TRADE_PERIOD,
            ],
            '無期限_開始日時' => [
                'now' => '2024-01-02 00:00:00',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => null,
                'errorCode' => null,
            ],
            '無期限_開始日時より後' => [
                'now' => '2024-12-31 23:59:59',
                'startAt' => '2024-01-02 00:00:00',
                'endAt' => null,
                'errorCode' => null,
            ],
        ];
    }

    #[DataProvider('params_test_validateExchange_交換所期間チェック')]
    public function test_validateExchange_交換所期間チェック(
        string $now,
        string $startAt,
        ?string $endAt,
        ?int $errorCode
    ) {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => null,
        ]);

        $nowCarbon = CarbonImmutable::parse($now);

        // usrExchangeLineupを作成
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'reset_at' => $nowCarbon->toDateTimeString(),
        ]);

        if ($errorCode) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            1,
            $nowCarbon
        );

        // Verify
        $this->assertTrue(true);
    }

    /**
     * 交換所とラインナップの整合性チェック
     */
    public function test_validateExchange_ラインナップ不整合でエラー()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $lineupGroupId1 = fake()->uuid();
        $lineupGroupId2 = fake()->uuid();

        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId1,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId2, // 異なるgroup_id
            'tradable_count' => null,
        ]);

        // usrExchangeLineupを作成
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'reset_at' => $now->toDateTimeString(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::EXCHANGE_LINEUP_MISMATCH);

        // Exercise
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            1,
            $now
        );
    }

    /**
     * 1回あたりの交換数上限チェック
     */
    public function test_validateExchange_1回あたりの交換数上限ちょうどは成功()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $lineupGroupId = fake()->uuid();

        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => null, // 総数上限なし
        ]);

        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'reset_at' => $now->toDateTimeString(),
        ]);

        // Exercise - 上限ちょうど
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            ExchangeConstant::MAX_TRADE_COUNT_PER_REQUEST,
            $now
        );

        // Verify
        $this->assertTrue(true);
    }

    /**
     * 1回あたりの交換数上限超過エラー
     */
    public function test_validateExchange_1回あたりの交換数上限超過でエラー()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $lineupGroupId = fake()->uuid();

        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => null, // 総数上限なし
        ]);

        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'reset_at' => $now->toDateTimeString(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        // Exercise - 上限超過
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            ExchangeConstant::MAX_TRADE_COUNT_PER_REQUEST + 1,
            $now
        );
    }

    /**
     * 交換上限チェック
     */
    public static function params_test_validateExchange_交換上限チェック()
    {
        return [
            '上限なし' => [
                'tradableCount' => null,
                'currentTradeCount' => 0,
                'tradeCount' => 5,
                'errorCode' => null,
            ],
            '上限内' => [
                'tradableCount' => 10,
                'currentTradeCount' => 5,
                'tradeCount' => 4,
                'errorCode' => null,
            ],
            '上限ちょうど' => [
                'tradableCount' => 10,
                'currentTradeCount' => 9,
                'tradeCount' => 1,
                'errorCode' => null,
            ],
            '上限超過' => [
                'tradableCount' => 10,
                'currentTradeCount' => 9,
                'tradeCount' => 2,
                'errorCode' => ErrorCode::EXCHANGE_LINEUP_TRADE_LIMIT_EXCEEDED,
            ],
            '既に上限到達' => [
                'tradableCount' => 10,
                'currentTradeCount' => 10,
                'tradeCount' => 1,
                'errorCode' => ErrorCode::EXCHANGE_LINEUP_TRADE_LIMIT_EXCEEDED,
            ],
        ];
    }

    #[DataProvider('params_test_validateExchange_交換上限チェック')]
    public function test_validateExchange_交換上限チェック(
        ?int $tradableCount,
        int $currentTradeCount,
        int $tradeCount,
        ?int $errorCode
    ) {
        // Setup
        $now = $this->fixTime('2024-01-05 12:00:00');
        $lineupGroupId = fake()->uuid();

        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => $tradableCount,
        ]);

        // 今月の04:00以降に作成されたレコードとしてリセットされないようにする
        $createdAt = CarbonImmutable::parse('2024-01-01 04:00:00');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => $currentTradeCount,
            'reset_at' => $createdAt->toDateTimeString(),
        ]);

        if ($errorCode) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            $tradeCount,
            $now
        );

        // Verify
        $this->assertTrue(true);
    }

    /**
     * コスト消費処理 - Coinのみ
     */
    public function test_consumeCosts_Coinコストを消費()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $mstExchange = MstExchange::factory()->create();
        $mstLineup = MstExchangeLineup::factory()->create();
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
        ]);

        // 初期Coinを設定
        UsrUserParameter::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'coin' => 500,
        ]);

        // Exercise
        $this->service->consumeCosts(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            collect([$mstCost->toEntity()]),
            2, // tradeCount = 2なので200Coin消費
            $now
        );

        // Verify
        $this->saveAll();
        $usrUserParameter = UsrUserParameter::where('usr_user_id', $this->usrUserId)->first();
        $this->assertEquals(300, $usrUserParameter->coin); // 500 - 200 = 300
    }

    /**
     * コスト消費処理 - Itemのみ
     */
    public function test_consumeCosts_Itemコストを消費()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $mstExchange = MstExchange::factory()->create();
        $mstItem = MstItem::factory()->create();
        $mstLineup = MstExchangeLineup::factory()->create();
        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Item',
            'cost_id' => $mstItem->id,
            'cost_amount' => 5,
        ]);

        // 初期Itemを設定
        UsrItem::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_item_id' => $mstItem->id,
            'amount' => 20,
        ]);

        // Exercise
        $this->service->consumeCosts(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            collect([$mstCost->toEntity()]),
            3, // tradeCount = 3なので15個消費
            $now
        );

        // Verify
        $this->saveAll();
        $usrItem = UsrItem::where('usr_user_id', $this->usrUserId)
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(5, $usrItem->amount); // 20 - 15 = 5
    }

    /**
     * 報酬準備処理
     */
    public function test_addRewards_報酬を準備()
    {
        // Setup
        $mstExchange = MstExchange::factory()->create();
        $mstItem = MstItem::factory()->create();
        $mstLineup = MstExchangeLineup::factory()->create();
        $mstReward = MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        // Exercise
        $this->service->addRewards(
            $mstExchange->id,
            $mstLineup->id,
            collect([$mstReward->toEntity()]),
            2 // tradeCount = 2なので報酬は20個
        );

        // Verify - RewardManagerに報酬が追加されていることを確認
        $rewardManager = app(RewardManager::class);
        $needToSendRewards = $rewardManager->getNeedToSendRewards();
        $this->assertCount(1, $needToSendRewards);

        /** @var ExchangeTradeReward $reward */
        $reward = $needToSendRewards->first();
        $this->assertInstanceOf(ExchangeTradeReward::class, $reward);
        $this->assertEquals('Item', $reward->getType());
        $this->assertEquals($mstItem->id, $reward->getResourceId());
        $this->assertEquals(20, $reward->getAmount()); // 10 * 2 = 20
    }

    /**
     * 交換実行 - 統合テスト
     */
    public function test_trade_交換実行統合テスト()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $platform = 1;

        // マスタデータ作成
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 5,
        ]);

        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
        ]);

        $mstItem = MstItem::factory()->create();
        $mstReward = MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstItem->id,
            'resource_amount' => 10,
        ]);

        // 初期リソース設定
        UsrUserParameter::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'coin' => 1000,
        ]);

        // Exercise
        $this->service->trade(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            2, // 2回交換
            $now,
            $platform
        );

        // Verify
        $this->saveAll();
        $this->saveAllLogModel();

        // Coin消費確認: 1000 - (100 * 2) = 800
        $usrUserParameter = UsrUserParameter::where('usr_user_id', $this->usrUserId)->first();
        $this->assertEquals(800, $usrUserParameter->coin);

        // 報酬確認: 10 * 2 = 20個
        $usrItem = UsrItem::where('usr_user_id', $this->usrUserId)
            ->where('mst_item_id', $mstItem->id)
            ->first();
        $this->assertEquals(20, $usrItem->amount);

        // 交換回数確認
        $usrExchangeLineupRepo = app(UsrExchangeLineupRepository::class);
        $usrExchangeLineup = $usrExchangeLineupRepo->get(
            $this->usrUserId,
            $mstLineup->id,
            $mstExchange->id
        );
        $this->assertNotNull($usrExchangeLineup);
        $this->assertEquals(2, $usrExchangeLineup->getTradeCount());

        // ログ確認
        $log = LogExchangeAction::where('usr_user_id', $this->usrUserId)->first();
        $this->assertNotNull($log);
        $this->assertEquals($mstLineup->id, $log->mst_exchange_lineup_id);
        $this->assertEquals(2, $log->getTradeCount());
    }

    /**
     * 交換実行 - 複数回交換で上限チェック
     */
    public function test_trade_複数回交換で交換回数が積算される()
    {
        // Setup
        $now = $this->fixTime('2024-01-05 00:00:00');
        $platform = 1;

        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 00:00:00',
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        $mstCost = MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
        ]);

        $mstItem = MstItem::factory()->create();
        $mstReward = MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstItem->id,
            'resource_amount' => 1,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'coin' => 10000,
        ]);

        // Exercise - 1回目: 3回交換
        $this->service->trade(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            3,
            $now,
            $platform
        );
        $this->saveAll();

        // Exercise - 2回目: 5回交換
        $this->service->trade(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            5,
            $now,
            $platform
        );
        $this->saveAll();

        // Verify - 交換回数が積算されている
        $usrExchangeLineupRepo = app(UsrExchangeLineupRepository::class);
        $usrExchangeLineup = $usrExchangeLineupRepo->get(
            $this->usrUserId,
            $mstLineup->id,
            $mstExchange->id
        );
        $this->assertEquals(8, $usrExchangeLineup->getTradeCount()); // 3 + 5 = 8

        // 3回目: 3回交換しようとすると上限超過エラー（8 + 3 > 10）
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::EXCHANGE_LINEUP_TRADE_LIMIT_EXCEEDED);

        $this->service->trade(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            3,
            $now,
            $platform
        );
    }

    /**
     * リセット判定のテスト（applyResetIfNeeded経由で動作確認）
     * needsResetはprivateメソッドのため、applyResetIfNeededの結果で判定
     */
    public static function params_test_applyResetIfNeeded_リセット判定(): array
    {
        return [
            // 基本的なリセット判定
            'reset_atが直近のリセット日時より前_リセット必要' => [
                'createdAt' => '2024-01-15 12:00:00', // 前月に作成
                'now' => '2024-02-15 12:00:00',
                'initialTradeCount' => 5,
                'expectedTradeCount' => 0,
            ],
            'reset_atが直近のリセット日時と同じ月_リセット不要' => [
                'createdAt' => '2024-02-05 12:00:00', // 今月に作成
                'now' => '2024-02-15 12:00:00',
                'initialTradeCount' => 5,
                'expectedTradeCount' => 5,
            ],
            'reset_atが直近のリセット日時より後_リセット不要' => [
                'createdAt' => '2024-02-01 04:30:00', // 今月04:00以降に作成
                'now' => '2024-02-01 05:00:00',
                'initialTradeCount' => 5,
                'expectedTradeCount' => 5,
            ],
            // 月境界のエッジケース（時刻はすべてUTCで指定、JST 04:00 = UTC 19:00 前日）
            '月初04:00より前_03:59:59_リセット不要' => [
                'createdAt' => '2024-01-15 03:00:00', // UTC（JST 12:00:00）
                'now' => '2024-01-31 18:59:59', // UTC（JST 2024-02-01 03:59:59）
                'initialTradeCount' => 5,
                'expectedTradeCount' => 5,
            ],
            '月初04:00ちょうど_リセット必要' => [
                'createdAt' => '2024-01-15 03:00:00', // UTC（JST 12:00:00）
                'now' => '2024-01-31 19:00:00', // UTC（JST 2024-02-01 04:00:00）
                'initialTradeCount' => 5,
                'expectedTradeCount' => 0,
            ],
            '月初04:00:01_リセット必要' => [
                'createdAt' => '2024-01-15 03:00:00', // UTC（JST 12:00:00）
                'now' => '2024-01-31 19:00:01', // UTC（JST 2024-02-01 04:00:01）
                'initialTradeCount' => 5,
                'expectedTradeCount' => 0,
            ],
        ];
    }

    #[DataProvider('params_test_applyResetIfNeeded_リセット判定')]
    public function test_applyResetIfNeeded_リセット判定(
        string $createdAt,
        string $now,
        int $initialTradeCount,
        int $expectedTradeCount
    ): void {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        $createdAtCarbon = CarbonImmutable::parse($createdAt);
        $nowCarbon = CarbonImmutable::parse($now);

        // 現在時刻を固定
        $this->fixTime($now);

        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => $initialTradeCount,
            'reset_at' => $createdAtCarbon->toDateTimeString(),
        ]);

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $nowCarbon, $mstExchange->toEntity());

        // Verify
        $this->assertEquals($expectedTradeCount, $usrExchangeLineup->getTradeCount());
    }

    /**
     * リセット処理のテスト - trade_countがリセットされる
     */
    public function test_applyResetIfNeeded_リセット必要な場合_trade_countが0になる(): void
    {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        // 前月の日時で作成
        $previousMonth = CarbonImmutable::parse('2024-01-15 12:00:00', 'Asia/Tokyo');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        // 今月の日時（2月）でリセット判定
        $now = $this->fixTime('2024-02-15 12:00:00');

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Verify
        $this->assertEquals(0, $usrExchangeLineup->getTradeCount());
        // reset_atにはリセット実行時の$nowが設定される
        $this->assertEquals($now->format('Y-m-d H:i:s'), $usrExchangeLineup->getResetAt());
    }

    /**
     * リセット処理のテスト - 同月内はリセットされない
     */
    public function test_applyResetIfNeeded_リセット不要な場合_trade_countは変わらない(): void
    {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        // 今月の日時で作成
        $now = $this->fixTime('2024-02-15 12:00:00');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $now->toDateTimeString(),
        ]);

        // 元のreset_atを保存
        $originalResetAt = $usrExchangeLineup->getResetAt();

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Verify
        $this->assertEquals(5, $usrExchangeLineup->getTradeCount());
        $this->assertEquals($originalResetAt, $usrExchangeLineup->getResetAt());
    }

    /**
     * 交換実行時にリセットが適用されることを確認
     */
    public function test_trade_リセット必要な場合_リセット後に交換が実行される(): void
    {
        // Setup
        $platform = 1;
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 3, // 月3回まで交換可能
        ]);

        MstExchangeCost::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'cost_type' => 'Coin',
            'cost_id' => null,
            'cost_amount' => 100,
        ]);

        $mstItem = MstItem::factory()->create();
        MstExchangeReward::factory()->create([
            'mst_exchange_lineup_id' => $mstLineup->id,
            'resource_type' => 'Item',
            'resource_id' => $mstItem->id,
            'resource_amount' => 1,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'coin' => 10000,
        ]);

        // 前月で3回交換済み（上限到達）の状態を作成
        $previousMonth = CarbonImmutable::parse('2024-01-15 12:00:00', 'Asia/Tokyo');
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 3, // 上限到達
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        // 今月の日時（2月）で交換を実行
        $now = $this->fixTime('2024-02-15 12:00:00');

        // Exercise - リセットが適用されて交換できるはず
        $this->service->trade(
            $this->usrUserId,
            $mstExchange->id,
            $mstLineup->id,
            2, // 2回交換
            $now,
            $platform
        );

        // Verify
        $this->saveAll();

        $usrExchangeLineupRepo = app(UsrExchangeLineupRepository::class);
        $usrExchangeLineup = $usrExchangeLineupRepo->get(
            $this->usrUserId,
            $mstLineup->id,
            $mstExchange->id
        );
        // リセット後に2回交換したので、trade_countは2
        $this->assertEquals(2, $usrExchangeLineup->getTradeCount());
        // reset_atにはリセット実行時の$nowが設定される
        $this->assertEquals($now->format('Y-m-d H:i:s'), $usrExchangeLineup->getResetAt());
    }

    /**
     * バリデーション時にもリセットが考慮されることを確認
     */
    public function test_validateExchange_リセット後に交換上限が再計算される(): void
    {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 3, // 月3回まで交換可能
        ]);

        // 前月で3回交換済み（上限到達）
        $previousMonth = CarbonImmutable::parse('2024-01-15 12:00:00');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 3,
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        // 今月の日時
        $now = $this->fixTime('2024-02-15 12:00:00');

        // applyResetIfNeededでリセットを適用（validateExchange呼び出し前にリセットが必要）
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Exercise & Verify - リセットが適用されるので、3回交換してもエラーにならない
        $this->service->validateExchange(
            $usrExchangeLineup,
            $mstExchange->toEntity(),
            $mstLineup->toEntity(),
            3,
            $now
        );

        $this->assertTrue(true);
    }

    /**
     * 年マタギのリセットテスト（12月→1月）
     */
    public function test_applyResetIfNeeded_年マタギで正しくリセットされる(): void
    {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        // 2024年12月に作成
        $december = CarbonImmutable::parse('2024-12-15 12:00:00', 'Asia/Tokyo');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $december->toDateTimeString(),
        ]);

        // 2025年1月1日 04:00 JSTでリセット判定
        $now = $this->fixTime('2025-01-01 04:00:00');

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Verify - trade_countが0にリセットされる
        $this->assertEquals(0, $usrExchangeLineup->getTradeCount());
    }

    /**
     * fetchResetUsrExchangeLineupsWithoutSyncModels - リセット適用済み一覧を取得
     */
    public function test_fetchResetUsrExchangeLineupsWithoutSyncModels_リセット適用済み一覧を取得(): void
    {
        // Setup - 2つの異なる交換所を作成
        $lineupGroupId1 = fake()->uuid();
        $mstExchange1 = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId1,
        ]);
        $mstLineup1 = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId1,
            'tradable_count' => 10,
        ]);

        $lineupGroupId2 = fake()->uuid();
        $mstExchange2 = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId2,
        ]);
        $mstLineup2 = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId2,
            'tradable_count' => 5,
        ]);

        // Exchange1: 前月に作成、交換3回（リセット対象）
        $previousMonth = CarbonImmutable::parse('2024-01-15 12:00:00', 'Asia/Tokyo');
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup1->id,
            'mst_exchange_id' => $mstExchange1->id,
            'trade_count' => 3,
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        // Exchange2: 今月に作成、交換2回（リセット不要）
        $currentMonth = CarbonImmutable::parse('2024-02-05 12:00:00', 'Asia/Tokyo');
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup2->id,
            'mst_exchange_id' => $mstExchange2->id,
            'trade_count' => 2,
            'reset_at' => $currentMonth->toDateTimeString(),
        ]);

        // Exercise - 今月（2月）の日時で一覧を取得
        $now = $this->fixTime('2024-02-15 12:00:00');
        $result = $this->service->fetchResetUsrExchangeLineupsWithoutSyncModels($this->usrUserId, $now);

        // Verify
        $this->assertCount(2, $result);

        // Exchange1はリセットされているはず（mstExchangeLineupIdでフィルタ）
        $lineup1 = $result->first(fn ($lineup) => $lineup->getMstExchangeLineupId() === $mstLineup1->id);
        $this->assertNotNull($lineup1);
        $this->assertEquals(0, $lineup1->getTradeCount()); // リセットされて0

        // Exchange2はリセットされていないはず
        $lineup2 = $result->first(fn ($lineup) => $lineup->getMstExchangeLineupId() === $mstLineup2->id);
        $this->assertNotNull($lineup2);
        $this->assertEquals(2, $lineup2->getTradeCount()); // そのまま2

        // DBには保存されていないことを確認
        $this->saveAll();
        $usrExchangeLineupRepo = app(UsrExchangeLineupRepository::class);
        $dbLineup1 = $usrExchangeLineupRepo->get(
            $this->usrUserId,
            $mstLineup1->id,
            $mstExchange1->id
        );
        $this->assertEquals(3, $dbLineup1->getTradeCount()); // DBにはまだ3が保存されている
    }

    /**
     * fetchResetUsrExchangeLineupsWithoutSyncModels - 空の一覧
     */
    public function test_fetchResetUsrExchangeLineupsWithoutSyncModels_レコードがない場合は空を返す(): void
    {
        // Setup - レコードなし
        $now = $this->fixTime('2024-02-15 12:00:00');

        // Exercise
        $result = $this->service->fetchResetUsrExchangeLineupsWithoutSyncModels($this->usrUserId, $now);

        // Verify
        $this->assertCount(0, $result);
    }

    /**
     * fetchResetUsrExchangeLineupsWithoutSyncModels - 同一交換所で複数ラインナップが取得できる
     * バグ修正テスト: 同じmst_exchange_idで複数のmst_exchange_lineup_idがある場合、全て取得できることを確認
     */
    public function test_fetchResetUsrExchangeLineupsWithoutSyncModels_同一交換所で複数ラインナップが全て取得できる(): void
    {
        // Setup - 1つの交換所に複数のラインナップを作成
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
        ]);

        // 同じ交換所に対して3つの異なるラインナップを作成
        $mstLineup1 = MstExchangeLineup::factory()->create([
            'id' => 'lineup_01',
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);
        $mstLineup2 = MstExchangeLineup::factory()->create([
            'id' => 'lineup_02',
            'group_id' => $lineupGroupId,
            'tradable_count' => 5,
        ]);
        $mstLineup3 = MstExchangeLineup::factory()->create([
            'id' => 'lineup_03',
            'group_id' => $lineupGroupId,
            'tradable_count' => 3,
        ]);

        // Exercise
        $now = $this->fixTime('2024-02-15 12:00:00');

        // 同一交換所に対して複数のラインナップの交換履歴を作成
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup1->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 1,
            'reset_at' => $now->toDateTimeString(),
        ]);
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup2->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 3,
            'reset_at' => $now->toDateTimeString(),
        ]);
        UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup3->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $now->toDateTimeString(),
        ]);

        $result = $this->service->fetchResetUsrExchangeLineupsWithoutSyncModels($this->usrUserId, $now);

        // Verify - 3つのラインナップ全てが取得できること
        $this->assertCount(3, $result);

        // 各ラインナップが正しい交換回数で取得できることを確認
        $lineup1 = $result->first(fn ($lineup) => $lineup->getMstExchangeLineupId() === 'lineup_01');
        $this->assertNotNull($lineup1);
        $this->assertEquals(1, $lineup1->getTradeCount());

        $lineup2 = $result->first(fn ($lineup) => $lineup->getMstExchangeLineupId() === 'lineup_02');
        $this->assertNotNull($lineup2);
        $this->assertEquals(3, $lineup2->getTradeCount());

        $lineup3 = $result->first(fn ($lineup) => $lineup->getMstExchangeLineupId() === 'lineup_03');
        $this->assertNotNull($lineup3);
        $this->assertEquals(5, $lineup3->getTradeCount());

        // 全てのラインナップが同じ交換所に属していることを確認
        $this->assertEquals($mstExchange->id, $lineup1->getMstExchangeId());
        $this->assertEquals($mstExchange->id, $lineup2->getMstExchangeId());
        $this->assertEquals($mstExchange->id, $lineup3->getMstExchangeId());
    }

    /**
     * ExchangeTradeTypeによるリセット判定（applyResetIfNeeded経由で動作確認）
     */
    public static function params_test_applyResetIfNeeded_ExchangeTradeType別リセット判定(): array
    {
        return [
            'NormalExchangeTrade_リセット対象' => [
                'exchangeTradeType' => ExchangeTradeType::NORMAL_EXCHANGE_TRADE->value,
                'expectedTradeCount' => 0, // リセットされる
            ],
            'EventExchangeTrade_リセット対象外' => [
                'exchangeTradeType' => ExchangeTradeType::EVENT_EXCHANGE_TRADE->value,
                'expectedTradeCount' => 5, // リセットされない
            ],
            'CharacterFragmentExchangeTrade_リセット対象外' => [
                'exchangeTradeType' => ExchangeTradeType::CHARACTER_FRAGMENT_EXCHANGE_TRADE->value,
                'expectedTradeCount' => 5, // リセットされない
            ],
        ];
    }

    #[DataProvider('params_test_applyResetIfNeeded_ExchangeTradeType別リセット判定')]
    public function test_applyResetIfNeeded_ExchangeTradeType別リセット判定(
        string $exchangeTradeType,
        int $expectedTradeCount
    ): void {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
            'exchange_trade_type' => $exchangeTradeType,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        // 前月の日時で作成
        $previousMonth = CarbonImmutable::parse('2024-01-15 03:00:00'); // UTC
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        // 今月の日時（2月）でリセット判定
        $now = $this->fixTime('2024-02-15 12:00:00');

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Verify
        $this->assertEquals($expectedTradeCount, $usrExchangeLineup->getTradeCount());
    }

    /**
     * EventExchangeTradeは月跨ぎしてもリセットされない
     */
    public function test_applyResetIfNeeded_EventExchangeTrade_月跨ぎしてもリセットされない_reset_at確認(): void
    {
        // Setup
        $lineupGroupId = fake()->uuid();
        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
            'exchange_trade_type' => ExchangeTradeType::EVENT_EXCHANGE_TRADE->value,
        ]);
        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        // 前月の日時で作成
        $previousMonth = CarbonImmutable::parse('2024-01-15 12:00:00');
        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchange->id,
            'trade_count' => 5,
            'reset_at' => $previousMonth->toDateTimeString(),
        ]);

        $originalResetAt = $usrExchangeLineup->getResetAt();

        // 今月の日時（2月）でリセット判定
        $now = $this->fixTime('2024-02-15 12:00:00');

        // Exercise
        $this->service->applyResetIfNeeded($usrExchangeLineup, $now, $mstExchange->toEntity());

        // Verify - リセットされていない
        $this->assertEquals(5, $usrExchangeLineup->getTradeCount());
        $this->assertEquals($originalResetAt, $usrExchangeLineup->getResetAt());
    }

    /**
     * needsResetテスト
     */
    public static function params_test_needsReset_リセット判定(): array
    {
        return [
            'NormalExchangeTrade_月跨ぎ_リセット必要' => [
                'resetAt' => '2024-01-15 12:00:00',
                'now' => '2024-02-15 12:00:00',
                'exchangeTradeType' => ExchangeTradeType::NORMAL_EXCHANGE_TRADE->value,
                'expected' => true,
            ],
            'NormalExchangeTrade_同月_リセット不要' => [
                'resetAt' => '2024-02-05 12:00:00',
                'now' => '2024-02-15 12:00:00',
                'exchangeTradeType' => ExchangeTradeType::NORMAL_EXCHANGE_TRADE->value,
                'expected' => false,
            ],
            'EventExchangeTrade_月跨ぎでもリセット不要' => [
                'resetAt' => '2024-01-15 12:00:00',
                'now' => '2024-02-15 12:00:00',
                'exchangeTradeType' => ExchangeTradeType::EVENT_EXCHANGE_TRADE->value,
                'expected' => false,
            ],
            'CharacterFragmentExchangeTrade_月跨ぎでもリセット不要' => [
                'resetAt' => '2024-01-15 12:00:00',
                'now' => '2024-02-15 12:00:00',
                'exchangeTradeType' => ExchangeTradeType::CHARACTER_FRAGMENT_EXCHANGE_TRADE->value,
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('params_test_needsReset_リセット判定')]
    public function test_needsReset_リセット判定(
        string $resetAt,
        string $now,
        string $exchangeTradeType,
        bool $expected
    ): void {
        // Setup
        $lineupGroupId = fake()->uuid();

        $mstExchange = MstExchange::factory()->create([
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => null,
            'lineup_group_id' => $lineupGroupId,
            'exchange_trade_type' => $exchangeTradeType,
        ]);
        $mstExchangeEntity = $mstExchange->toEntity();

        $mstLineup = MstExchangeLineup::factory()->create([
            'group_id' => $lineupGroupId,
            'tradable_count' => 10,
        ]);

        $this->fixTime($now);

        $usrExchangeLineup = UsrExchangeLineup::factory()->create([
            'usr_user_id' => $this->usrUserId,
            'mst_exchange_lineup_id' => $mstLineup->id,
            'mst_exchange_id' => $mstExchangeEntity->getId(),
            'trade_count' => 5,
            'reset_at' => $resetAt,
        ]);

        // Exercise
        $result = $this->execPrivateMethod(
            $this->service,
            'needsReset',
            [$usrExchangeLineup, $mstExchangeEntity]
        );

        // Verify
        $this->assertEquals($expected, $result);
    }
}
