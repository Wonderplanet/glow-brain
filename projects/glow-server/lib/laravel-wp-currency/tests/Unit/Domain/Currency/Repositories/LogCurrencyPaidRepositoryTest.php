<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyAdminService;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogCurrencyPaidRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogCurrencyPaidRepository $logCurrencyPaidRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
    }

    public function tearDown(): void
    {
        $this->setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function insertPaidLog_ログが追加されていること()
    {
        // Exercise
        $id = $this->logCurrencyPaidRepository->insertPaidLog(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            1,
            'currency_paid_id',
            'receipt_unique_id',
            false,
            LogCurrencyPaid::QUERY_INSERT,
            '100.000000',
            100,
            '1.00000000',
            101,
            'JPY',
            100,
            10,
            110,
            new Trigger('pf_log', 'unittest', 'unittest name', 'unittest details')
        );

        // Verify
        $logCurrencyPaid = $this->logCurrencyPaidRepository->findById($id);
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals('currency_paid_id', $logCurrencyPaid->currency_paid_id);
        $this->assertEquals('receipt_unique_id', $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals(0, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(100, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(110, $logCurrencyPaid->current_amount);
        $this->assertEquals('pf_log', $logCurrencyPaid->trigger_type);
        $this->assertEquals('unittest', $logCurrencyPaid->trigger_id);
        $this->assertEquals('unittest name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('unittest details', $logCurrencyPaid->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyPaid->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyPaid->request_id);
    }

    #[Test]
    #[DataProvider('getCurrencyAggregationByJPYPlatformData')]
    public function getCurrencyAggregationByJPY_日本時間基準で取得できているか(
        ?string $billingPlatform,
        int $expectedPaidSumAmount,
        int $expectedConsumeSumAmount,
        int $expectedDeleteSumAmount,
    ): void {
        // setUp
        $this->setupTestData();

        // Exercise
        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)で取得
        $createdAtJst = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $result = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByJPY($createdAtJst, false, $billingPlatform);

        // 各トリガータイプのデータを取得
        $resultTriggerInsert = $result->first(function ($row) {
            return $row['trigger_type'] === 'insert';
        });
        $resultTriggerConsume = $result->first(function ($row) {
            return $row['trigger_type'] === 'consume';
        });
        $resultTriggerDelete = $result->first(function ($row) {
            return $row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER;
        });

        // Verify
        $this->assertCount(3, $result);
        // 購入ログ
        $this->assertEquals('insert', $resultTriggerInsert['trigger_type']);
        $this->assertEquals('1.00000000', $resultTriggerInsert['price_per_amount']);
        $this->assertEquals($expectedPaidSumAmount, $resultTriggerInsert['sum_amount']);
        // 消費ログ
        $this->assertEquals('consume', $resultTriggerConsume['trigger_type']);
        $this->assertEquals('1.00000000', $resultTriggerConsume['price_per_amount']);
        $this->assertEquals($expectedConsumeSumAmount, $resultTriggerConsume['sum_amount']);
        // 論理削除ログ
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $resultTriggerDelete['trigger_type']);
        $this->assertEquals('1.00000000', $resultTriggerDelete['price_per_amount']);
        $this->assertEquals($expectedDeleteSumAmount, $resultTriggerDelete['sum_amount']);
    }

    /**
     * @return array
     */
    public static function getCurrencyAggregationByJPYPlatformData(): array
    {
        return [
            '全プラットフォーム' => [null, 110, -57, -53],
            'AppleStore' => [CurrencyConstants::PLATFORM_APPSTORE, 100, -50, -50],
            'GooglePlay' => [CurrencyConstants::PLATFORM_GOOGLEPLAY, 10, -7, -3],
        ];
    }

    #[Test]
    public function getCurrencyAggregationByJPY_存在しないプラットフォームが指定された(): void
    {
        // setUp
        $this->setupTestData();

        // Exercise
        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)で取得
        $createdAtJst = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $result = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByJPY($createdAtJst, false, 'unknown');

        // Verify
        $this->assertCount(0, $result);
    }

    #[Test]
    public function getCurrencyAggregationByNotJPY_日本時間基準で取得できているか(): void
    {
        // setUp
        $this->setupTestData();

        // Exercise
        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)で取得
        $createdAtJst = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $result = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByNotJPY($createdAtJst, false);

        // 各通貨コードのデータを取得
        $resultEurTriggerInsert = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === 'insert';
        });
        $resultEurTriggerConsume = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === 'consume';
        });
        $resultEurTriggerDelete = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER;
        });
        $resultUsdTriggerInsert = $result->first(function ($row) {
            return $row['currency_code'] === 'USD'
                && $row['trigger_type'] === 'insert';
        });
        $resultUsdTriggerConsume = $result->first(function ($row) {
            return $row['currency_code'] === 'USD'
                && $row['trigger_type'] === 'consume';
        });
        $resultUsdTriggerDelete = $result->first(function ($row) {
            return $row['currency_code'] === 'USD'
                && $row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER;
        });

        // Verify
        // 取得データがUSD(購入+消費)+EUR(購入+削除)で4件になっていること
        $this->assertEquals(4, $result->count());
        // 通貨コード'EUR'のチェック
        $this->assertEquals('insert', $resultEurTriggerInsert['trigger_type']);
        $this->assertEquals('1.00000000', $resultEurTriggerInsert['price_per_amount']);
        $this->assertEquals('200', $resultEurTriggerInsert['sum_amount']);
        $this->assertEquals('EUR', $resultEurTriggerInsert['currency_code']);
        $this->assertEquals(Trigger::TRIGGER_TYPE_DELETE_USER, $resultEurTriggerDelete['trigger_type']);
        $this->assertEquals('1.00000000', $resultEurTriggerDelete['price_per_amount']);
        $this->assertEquals('-200', $resultEurTriggerDelete['sum_amount']);
        $this->assertEquals('EUR', $resultEurTriggerDelete['currency_code']);
        $this->assertEmpty($resultEurTriggerConsume);

        // 通貨コード'USD'のチェック
        $this->assertEquals('insert', $resultUsdTriggerInsert['trigger_type']);
        $this->assertEquals('1.00000000', $resultUsdTriggerInsert['price_per_amount']);
        $this->assertEquals('100', $resultUsdTriggerInsert['sum_amount']);
        $this->assertEquals('USD', $resultUsdTriggerInsert['currency_code']);
        $this->assertEquals('consume', $resultUsdTriggerConsume['trigger_type']);
        $this->assertEquals('1.00000000', $resultUsdTriggerConsume['price_per_amount']);
        $this->assertEquals('-30', $resultUsdTriggerConsume['sum_amount']);
        $this->assertEquals('USD', $resultUsdTriggerConsume['currency_code']);
        $this->assertEmpty($resultUsdTriggerDelete);
    }

    #[Test]
    #[DataProvider('getCurrencyAggregationByJPYSandboxData')]
    public function getCurrencyAggregationByJPY_サンドボックスデータ取得可否(
        bool $isIncludeSandbox,
        string $expectedInsert,
        string $expectedConsume,
        string $expectedDelete
    ): void {
        // setUp
        $this->setupTestData();

        // Exercise
        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)で取得
        $createdAtJst = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $result = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByJPY($createdAtJst, $isIncludeSandbox, null);

        // Verify
        $this->assertEquals(3, count($result));
        $resultTriggerInsert = $result->first(function ($row) {
            return $row['trigger_type'] === 'insert';
        });
        $resultTriggerConsume = $result->first(function ($row) {
            return $row['trigger_type'] === 'consume';
        });
        $resultTriggerDelete = $result->first(function ($row) {
            return $row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER;
        });
        // 消費ログにsandboxのデータが反映されているか
        $this->assertEquals($expectedInsert, $resultTriggerInsert['sum_amount']);
        $this->assertEquals($expectedConsume, $resultTriggerConsume['sum_amount']);
        $this->assertEquals($expectedDelete, $resultTriggerDelete['sum_amount']);
    }

    /**
     * @return array[]
     */
    public static function getCurrencyAggregationByJPYSandboxData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '110', '-57', '-53'],
            'サンドボックスデータを含める' => [true, '111', '-59', '-56']
        ];
    }

    #[Test]
    #[DataProvider('getCurrencyAggregationByNotJPYSandboxData')]
    public function getCurrencyAggregationByNotJPY_サンドボックスデータ取得可否(
        bool $isIncludeSandbox,
        string $expectedEurInsert,
        string $expectedEurConsume,
        string $expectedEurDelete,
        string $expectedUsdInsert,
        string $expectedUsdConsume
    ): void {
        // setUp
        $this->setupTestData();

        // EURの消費ログを1件だけ作成
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-0',
            receiptUniqueId: 'receipt_unique_id_1-0',
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'EUR',
            changeAmount: -1,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:30:00'
        );

        // Exercise
        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)で取得
        $createdAtJst = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $result = $this->logCurrencyPaidRepository
            ->getCurrencyAggregationByNotJPY($createdAtJst, $isIncludeSandbox);

        // 各通貨コードのデータを取得
        $resultEurTriggerInsert = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === 'insert';
        });
        $resultEurTriggerConsume = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === 'consume';
        });
        $resultEurTriggerDelete = $result->first(function ($row) {
            return $row['currency_code'] === 'EUR'
                && $row['trigger_type'] === Trigger::TRIGGER_TYPE_DELETE_USER;
        });
        $resultUsdTriggerInsert = $result->first(function ($row) {
            return $row['currency_code'] === 'USD'
                && $row['trigger_type'] === 'insert';
        });
        $resultUsdTriggerConsume = $result->first(function ($row) {
            return $row['currency_code'] === 'USD'
                && $row['trigger_type'] === 'consume';
        });

        // Verify
        // 通貨コード'EUR'のチェック
        $this->assertEquals($expectedEurInsert, $resultEurTriggerInsert['sum_amount']);
        $this->assertEquals($expectedEurConsume, $resultEurTriggerConsume['sum_amount']);
        $this->assertEquals($expectedEurDelete, $resultEurTriggerDelete['sum_amount']);

        // 通貨コード'USD'のチェック
        $this->assertEquals($expectedUsdInsert, $resultUsdTriggerInsert['sum_amount']);
        $this->assertEquals($expectedUsdConsume, $resultUsdTriggerConsume['sum_amount']);
    }

    /**
     * @return array[]
     */
    public static function getCurrencyAggregationByNotJPYSandboxData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '200', '-1', '-200', '100', '-30'],
            'サンドボックスデータを含める' => [true, '210', '-21', '-230', '101', '-33']
        ];
    }

    #[Test]
    #[DataProvider('getCollaboAggregationData')]
    public function getCollaboAggregation_コラボ集計データを作成(
        bool $isIncludeSandbox,
        int $addedExpectedG1,
        int $addedExpectedS1
    ): void {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-01-28 00:00:00 〜 2023-02-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -101,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        //    消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -110,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        //    通貨別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -102,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 00:00:00+09:00',
        );
        //    通貨別&消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -99,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        //     単価別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '2',
            currencyCode: 'JPY',
            changeAmount: -103,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 00:00:00+09:00',
        );
        //     単価別&消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '2',
            currencyCode: 'JPY',
            changeAmount: -111,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        //    終了前
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -104,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-10 23:59:59+09:00',
        );
        //    複数の対象ID
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -105,
            triggerType: 'gacha',
            triggerId: 'g-1-2',
            triggerName: 'コラボ1-2',
            createdAtJstStr: '2023-01-30 23:59:59+09:00',
        );
        //    複数の対象ID&消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -106,
            triggerType: 'gacha',
            triggerId: 'g-1-2',
            triggerName: 'コラボ1-2',
            createdAtJstStr: '2023-02-01 23:59:59+09:00',
        );
        // 集計対象ショップ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -206,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -207,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        //    消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -210,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-02 00:00:00+09:00',
        );
        //    通貨別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -208,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 00:00:00+09:00',
        );
        //    通貨別＆消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -211,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-05 00:00:00+09:00',
        );
        //   単価別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '2',
            currencyCode: 'JPY',
            changeAmount: -209,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 00:00:00+09:00',
        );
        //   単価別&消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '2',
            currencyCode: 'JPY',
            changeAmount: -212,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-06 00:00:00+09:00',
        );
        //   終了前
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -213,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-10 23:59:59+09:00',
        );
        //    複数の対象ID
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -214,
            triggerType: 'product',
            triggerId: 's-1-2',
            triggerName: 'コラボ1-2',
            createdAtJstStr: '2023-01-31 23:59:59+09:00',
        );
        //    複数の対象ID&消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -215,
            triggerType: 'product',
            triggerId: 's-1-2',
            triggerName: 'コラボ1-2',
            createdAtJstStr: '2023-02-01 23:59:59+09:00',
        );
        // 集計対象外
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-2',
            triggerName: 'コラボ2',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -200,
            triggerType: 'product',
            triggerId: 's-2',
            triggerName: 'コラボ2',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        //  消費していない
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 200,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 期間外
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-11 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -200,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-11 00:00:00+09:00',
        );
        // sandboxデータ
        $this->makeLogCurrencyPaidRecord(
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -111,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -222,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ2',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );

        // Exercise
        $startAt = Carbon::create(2023, 1, 28, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2023, 2, 10, 23, 59, 59, 'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'product', 'ids' => ['s-1', 's-1-2']],
        ];

        $result = $this->logCurrencyPaidRepository
            ->getCollaboAggregation(
                $startAt,
                $endAt,
                $searchTriggers,
                $isIncludeSandbox,
                []
            );

        // Verify
        $this->assertEquals(16, count($result));

        // 組み合わせでの集計
        //   change_amountが集計されているか
        $expected = [
            ['gacha', 'g-1', '2023-01', 'JPY', '1', 100 + 101 + $addedExpectedG1],
            ['gacha', 'g-1', '2023-02', 'JPY', '1', 110 + 104],
            ['gacha', 'g-1', '2023-01', 'USD', '1', 102],
            ['gacha', 'g-1', '2023-02', 'USD', '1', 99],
            ['gacha', 'g-1', '2023-01', 'JPY', '2', 103],
            ['gacha', 'g-1', '2023-02', 'JPY', '2', 111],
            ['gacha', 'g-1-2', '2023-01', 'JPY', '1', 105],
            ['gacha', 'g-1-2', '2023-02', 'JPY', '1', 106],
            ['product', 's-1', '2023-01', 'JPY', '1', 206 + 207 + $addedExpectedS1],
            ['product', 's-1', '2023-02', 'JPY', '1', 210 + 213],
            ['product', 's-1', '2023-01', 'USD', '1', 208],
            ['product', 's-1', '2023-02', 'USD', '1', 211],
            ['product', 's-1', '2023-01', 'JPY', '2', 209],
            ['product', 's-1', '2023-02', 'JPY', '2', 212],
            ['product', 's-1-2', '2023-01', 'JPY', '1', 214],
            ['product', 's-1-2', '2023-02', 'JPY', '1', 215],
        ];
        // データの照合しやすいようにCollectionに変換
        $result = collect($result);
        foreach ($expected as $expect) {
            $record = $result
                ->where('trigger_type', $expect[0])
                ->where('trigger_id', $expect[1])
                ->where('year_month_created_at', $expect[2])
                ->where('currency_code', $expect[3])
                ->where('price_per_amount', $expect[4])
                ->first();
            $this->assertEquals($expect[5], $record['sum_amount']);
        }
    }

    /**
     * @return array
     */
    public static function getCollaboAggregationData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, 0, 0],
            'サンドボックスデータを含める' => [true, 111, 222]
        ];
    }

    #[Test]
    public function getCollaboAggregation_一次通貨返却があった場合のチェック_全返却のみ(): void
    {
        // Setup
        $revertLogCurrencyPaids = [];
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-01-28 00:00:00 〜 2023-02-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -101,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 消費月別(一次通貨返却対象)
        // 全返却
        $revertLogCurrencyPaidId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -110,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 110,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId1,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId1,
        ];
        // 集計対象ショップ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -206,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 一次通貨返却対象
        // 全返却
        $revertLogCurrencyPaidId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -207,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 207,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId2,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId2,
        ];
        //    消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -210,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-02 00:00:00+09:00',
        );

        // Exercise
        $startAt = Carbon::create(2023, 1, 28, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2023, 2, 10, 23, 59, 59, 'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'product', 'ids' => ['s-1', 's-1-2']],
        ];

        $result = $this->logCurrencyPaidRepository
            ->getCollaboAggregation(
                $startAt,
                $endAt,
                $searchTriggers,
                false,
                $revertLogCurrencyPaids,
            );

        // Verify
        $this->assertCount(3, $result);

        //  一次通貨返却対象はガチャ2月分の110個消費とショップ1月分207個消費
        //  それぞれの集計結果に返却分が除外されているかチェック
        //   ガチャ 1月分集計分
        $resultG1_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('gacha', $resultG1_01['trigger_type']);
        $this->assertEquals('g-1', $resultG1_01['trigger_id']);
        $this->assertEquals('2023-01', $resultG1_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultG1_01['currency_code']);
        $this->assertEquals('1.00000000', $resultG1_01['price_per_amount']);
        $this->assertEquals(201, $resultG1_01['sum_amount']);
        //   ガチャ 2月分集計分
        $resultG1_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-02');
        //   2月は返却された為集計結果がないのでnullになること
        $this->assertNull($resultG1_02);
        //   ショップ 1月分集計チェック
        $resultS1_23_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('product', $resultS1_23_01['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_01['trigger_id']);
        $this->assertEquals('2023-01', $resultS1_23_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_01['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_01['price_per_amount']);
        $this->assertEquals(206, $resultS1_23_01['sum_amount']);
        //   ショップ 2月分集計チェック
        $resultS1_23_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-02');
        $this->assertEquals('product', $resultS1_23_02['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_02['trigger_id']);
        $this->assertEquals('2023-02', $resultS1_23_02['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_02['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_02['price_per_amount']);
        $this->assertEquals(210, $resultS1_23_02['sum_amount']);
    }

    #[Test]
    public function getCollaboAggregation_一次通貨返却があった場合のチェック_一部返却のみ(): void
    {
        // Setup
        $revertLogCurrencyPaids = [];
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-01-28 00:00:00 〜 2023-02-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -101,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 消費月別(一次通貨返却対象)
        // 一部返却
        $revertLogCurrencyPaidId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -110,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 10,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId1,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId1,
        ];
        // 集計対象ショップ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -206,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 一次通貨返却対象
        // 一部返却
        $revertLogCurrencyPaidId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -207,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 7,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId2,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId2,
        ];
        //    消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -210,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-02 00:00:00+09:00',
        );

        // Exercise
        $startAt = Carbon::create(2023, 1, 28, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2023, 2, 10, 23, 59, 59, 'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'product', 'ids' => ['s-1', 's-1-2']],
        ];

        $result = $this->logCurrencyPaidRepository
            ->getCollaboAggregation(
                $startAt,
                $endAt,
                $searchTriggers,
                false,
                $revertLogCurrencyPaids,
            );

        // Verify
        $this->assertCount(4, $result);

        //  一次通貨返却対象はガチャ2月分の110個消費とショップ1月分207個消費
        //  それぞれの集計結果に返却分が除外されているかチェック
        //   ガチャ 1月分集計分
        $resultG1_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('gacha', $resultG1_01['trigger_type']);
        $this->assertEquals('g-1', $resultG1_01['trigger_id']);
        $this->assertEquals('2023-01', $resultG1_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultG1_01['currency_code']);
        $this->assertEquals('1.00000000', $resultG1_01['price_per_amount']);
        $this->assertEquals(201, $resultG1_01['sum_amount']);
        //   ガチャ 2月分集計分
        $resultG1_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-02');
        $this->assertEquals('gacha', $resultG1_02['trigger_type']);
        $this->assertEquals('g-1', $resultG1_02['trigger_id']);
        $this->assertEquals('2023-02', $resultG1_02['year_month_created_at']);
        $this->assertEquals('JPY', $resultG1_02['currency_code']);
        $this->assertEquals('1.00000000', $resultG1_02['price_per_amount']);
        $this->assertEquals(100, $resultG1_02['sum_amount']);
        //   ショップ 1月分集計チェック
        $resultS1_23_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('product', $resultS1_23_01['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_01['trigger_id']);
        $this->assertEquals('2023-01', $resultS1_23_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_01['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_01['price_per_amount']);
        // 一部返却があったため、206と200で合計406になる
        $this->assertEquals(406, $resultS1_23_01['sum_amount']);
        //   ショップ 2月分集計チェック
        $resultS1_23_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-02');
        $this->assertEquals('product', $resultS1_23_02['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_02['trigger_id']);
        $this->assertEquals('2023-02', $resultS1_23_02['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_02['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_02['price_per_amount']);
        $this->assertEquals(210, $resultS1_23_02['sum_amount']);
    }

    #[Test]
    public function getCollaboAggregation_一次通貨返却があった場合のチェック_全返却と一部返却の混合(): void
    {
        // Setup
        $revertLogCurrencyPaids = [];
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-01-28 00:00:00 〜 2023-02-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -101,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 消費月別(一次通貨返却対象)
        // 全返却
        $revertLogCurrencyPaidId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -110,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 110,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-02-01 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId1,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId1,
        ];
        // 集計対象ショップ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -206,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-28 00:00:00+09:00',
        );
        // 一次通貨返却対象
        // 一部返却
        $revertLogCurrencyPaidId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -207,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: 7,
            triggerType: 'revert_currency',
            createdAtJstStr: '2023-01-30 00:00:00+09:00',
        );
        $revertLogCurrencyPaids[] = [
            'log_currency_paid_id' => $revertLogId2,
            'revert_log_currency_paid_id' => $revertLogCurrencyPaidId2,
        ];
        //    消費月別
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -210,
            triggerType: 'product',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-02-02 00:00:00+09:00',
        );

        // Exercise
        $startAt = Carbon::create(2023, 1, 28, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2023, 2, 10, 23, 59, 59, 'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'product', 'ids' => ['s-1', 's-1-2']],
        ];

        $result = $this->logCurrencyPaidRepository
            ->getCollaboAggregation(
                $startAt,
                $endAt,
                $searchTriggers,
                false,
                $revertLogCurrencyPaids,
            );

        // Verify
        $this->assertCount(3, $result);

        //  一次通貨返却対象はガチャ2月分の110個消費とショップ1月分207個消費
        //  それぞれの集計結果に返却分が除外されているかチェック
        //   ガチャ 1月分集計分
        $resultG1_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('gacha', $resultG1_01['trigger_type']);
        $this->assertEquals('g-1', $resultG1_01['trigger_id']);
        $this->assertEquals('2023-01', $resultG1_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultG1_01['currency_code']);
        $this->assertEquals('1.00000000', $resultG1_01['price_per_amount']);
        $this->assertEquals(201, $resultG1_01['sum_amount']);
        //   ガチャ 2月分集計分
        $resultG1_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 'g-1' && $row['year_month_created_at'] === '2023-02');
        //   2月は返却された為集計結果がないのでnullになること
        $this->assertNull($resultG1_02);
        //   ショップ 1月分集計チェック
        $resultS1_23_01 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-01');
        $this->assertEquals('product', $resultS1_23_01['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_01['trigger_id']);
        $this->assertEquals('2023-01', $resultS1_23_01['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_01['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_01['price_per_amount']);
        // 一部返却があったため、206と200で合計406になる
        $this->assertEquals(406, $resultS1_23_01['sum_amount']);
        //   ショップ 2月分集計チェック
        $resultS1_23_02 = collect($result)->first(fn ($row) => $row['trigger_id'] === 's-1' && $row['year_month_created_at'] === '2023-02');
        $this->assertEquals('product', $resultS1_23_02['trigger_type']);
        $this->assertEquals('s-1', $resultS1_23_02['trigger_id']);
        $this->assertEquals('2023-02', $resultS1_23_02['year_month_created_at']);
        $this->assertEquals('JPY', $resultS1_23_02['currency_code']);
        $this->assertEquals('1.00000000', $resultS1_23_02['price_per_amount']);
        $this->assertEquals(210, $resultS1_23_02['sum_amount']);
    }

    /**
     * テストデータを作成する
     */
    private function setupTestData(): void
    {
        // 期間内データ
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-1',
            receiptUniqueId: 'receipt_unique_id_1-1',
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-1',
            receiptUniqueId: 'receipt_unique_id_1-1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            beforeAmount: 100,
            changeAmount: -50,
            currentAmount: 50,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-1',
            receiptUniqueId: 'receipt_unique_id_1-1',
            query: LogCurrencyPaid::QUERY_DELETE,
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            beforeAmount: 50,
            changeAmount: -50,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            currencyPaidId: 'currency_paid_id_1-4',
            receiptUniqueId: 'receipt_unique_id_1-4',
            purchasePrice: '10.000000',
            purchaseAmount: 10,
            pricePerAmount: '1.00000000',
            changeAmount: 10,
            currentAmount: 10,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            currencyPaidId: 'currency_paid_id_1-4',
            receiptUniqueId: 'receipt_unique_id_1-4',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '10.000000',
            purchaseAmount: 10,
            pricePerAmount: '1.00000000',
            beforeAmount: 10,
            changeAmount: -7,
            currentAmount: 3,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            currencyPaidId: 'currency_paid_id_1-4',
            receiptUniqueId: 'receipt_unique_id_1-4',
            query: LogCurrencyPaid::QUERY_DELETE,
            purchasePrice: '10.000000',
            purchaseAmount: 10,
            pricePerAmount: '1.00000000',
            beforeAmount: 3,
            changeAmount: -3,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '2',
            currencyPaidId: 'currency_paid_id_2-1',
            receiptUniqueId: 'receipt_unique_id_2-1',
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '2',
            currencyPaidId: 'currency_paid_id_2-1',
            receiptUniqueId: 'receipt_unique_id_2-1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            currencyCode: 'USD',
            beforeAmount: 100,
            changeAmount: -30,
            currentAmount: 70,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '3',
            currencyPaidId: 'currency_paid_id_3-1',
            receiptUniqueId: 'receipt_unique_id_3-1',
            purchasePrice: '200.000000',
            purchaseAmount: 200,
            pricePerAmount: '1.00000000',
            currencyCode: 'EUR',
            changeAmount: 200,
            currentAmount: 200,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '3',
            currencyPaidId: 'currency_paid_id_4-1',
            receiptUniqueId: 'receipt_unique_id_4-1',
            query: LogCurrencyPaid::QUERY_DELETE,
            purchasePrice: '200.000000',
            purchaseAmount: 200,
            pricePerAmount: '1.00000000',
            currencyCode: 'EUR',
            beforeAmount: 200,
            changeAmount: -200,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        //    登録数、単価が0のデータ
        //       パス商品などで発生する場合がある
        $this->makeLogCurrencyPaidRecord(
            seqNo: 2,
            currencyPaidId: 'currency_paid_id_1-10',
            receiptUniqueId: 'receipt_unique_id_1-10',
            query: LogCurrencyPaid::QUERY_INSERT,
            purchasePrice: '100.000000',
            purchaseAmount: 0,
            pricePerAmount: '0.00000000',
            beforeAmount: 0,
            changeAmount: 0,
            triggerType: 'insert',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '3',
            seqNo: 2,
            currencyPaidId: 'currency_paid_id_3-10',
            receiptUniqueId: 'receipt_unique_id_3-10',
            purchasePrice: '200.000000',
            purchaseAmount: 0,
            pricePerAmount: '0.00000000',
            currencyCode: 'EUR',
            beforeAmount: 200,
            changeAmount: 0,
            currentAmount: 200,
            triggerType: 'insert',
            triggerId: 's-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-31 23:59:59'
        );

        // 期間外データ
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-2',
            receiptUniqueId: 'receipt_unique_id_1-2',
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2024-01-01 00:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '2',
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            currencyPaidId: 'currency_paid_id_2-2',
            receiptUniqueId: 'receipt_unique_id_2-2',
            purchasePrice: '100.000000',
            purchaseAmount: 100,
            pricePerAmount: '1.00000000',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2024-01-01 00:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '3',
            currencyPaidId: 'currency_paid_id_3-2',
            receiptUniqueId: 'receipt_unique_id_3-2',
            purchasePrice: '200.000000',
            purchaseAmount: 200,
            pricePerAmount: '1.00000000',
            currencyCode: 'EUR',
            changeAmount: 200,
            currentAmount: 200,
            createdAtJstStr: '2024-01-01 00:00:00'
        );

        // isSandbox=trueのデータを作成
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-1',
            receiptUniqueId: 'receipt_unique_id_1-1',
            isSandbox: true,
            pricePerAmount: '1',
            changeAmount: 1,
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-2',
            receiptUniqueId: 'receipt_unique_id_1-2',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -2,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-3',
            receiptUniqueId: 'receipt_unique_id_1-3',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_DELETE,
            pricePerAmount: '1',
            changeAmount: -3,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-1',
            receiptUniqueId: 'receipt_unique_id_1-1',
            isSandbox: true,
            pricePerAmount: '1',
            currencyCode: 'EUR',
            changeAmount: 10,
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-2',
            receiptUniqueId: 'receipt_unique_id_1-2',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'EUR',
            changeAmount: -20,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_1-3',
            receiptUniqueId: 'receipt_unique_id_1-3',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_DELETE,
            pricePerAmount: '1',
            currencyCode: 'EUR',
            changeAmount: -30,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_2-1',
            receiptUniqueId: 'receipt_unique_id_2-1',
            isSandbox: true,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: 1,
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_2-2',
            receiptUniqueId: 'receipt_unique_id_2-2',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -1,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:30:00'
        );
        $this->makeLogCurrencyPaidRecord(
            currencyPaidId: 'currency_paid_id_2-3',
            receiptUniqueId: 'receipt_unique_id_2-3',
            isSandbox: true,
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -2,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:30:00'
        );
    }

    /**
     * log_currency_paids レコード作成
     */
    private function makeLogCurrencyPaidRecord(
        $userId = '1',
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS,
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE,
        $seqNo = 1,
        $currencyPaidId = '1',
        $receiptUniqueId = 'receipt_unique_id_1',
        $isSandbox = false,
        $query = LogCurrencyPaid::QUERY_INSERT,
        $purchasePrice = '0',
        $purchaseAmount = 0,
        $pricePerAmount = '0',
        $vipPoint = 0,
        $currencyCode = 'JPY',
        $beforeAmount = 0,
        $changeAmount = 0,
        $currentAmount = 0,
        $triggerType = 'insert',
        $triggerId = '',
        $triggerName = '',
        $triggerDetail = '',
        $createdAtJstStr = '2023-01-01 00:00:00'
    ): string {
        // 作成日の日時設定
        // $createdAtJstStrを日本時間として生成、UTC時間に変換する
        // 例:日本時間 2023-01-01 00:00:00 -> UTC 2022-12-31 15:00:00 としてcreated_at,updated_atに保存
        $now = Carbon::create($createdAtJstStr, CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $now->setTimezone('UTC');
        $this->setTestNow($now);
        return $this->logCurrencyPaidRepository->insertPaidLog(
            $userId,
            $osPlatform,
            $billingPlatform,
            $seqNo,
            $currencyPaidId,
            $receiptUniqueId,
            $isSandbox,
            $query,
            $purchasePrice,
            $purchaseAmount,
            $pricePerAmount,
            $vipPoint,
            $currencyCode,
            $beforeAmount,
            $changeAmount,
            $currentAmount,
            new Trigger($triggerType, $triggerId, $triggerName, $triggerDetail)
        );
    }
}
