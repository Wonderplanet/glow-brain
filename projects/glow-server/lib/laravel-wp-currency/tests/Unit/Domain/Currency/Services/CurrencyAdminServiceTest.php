<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Mockery;
use ReflectionClass;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Entities\AddFreeCurrencyBatchTrigger;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyAdminTrigger;
use WonderPlanet\Domain\Currency\Entities\CurrencyRevertTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Enums\FreeCurrencyType;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryFreeLog;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyDailyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryFreeLogRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryPaidLogRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyRevertHistoryRepository;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;
use WonderPlanet\Domain\Currency\Repositories\UnionLogCurrencyRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyAdminService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregation;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregationByForeignCountry;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyPaidDetail;
use WonderPlanet\Domain\Currency\Utils\Scrape\ForeignCurrencyRateScrape;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class CurrencyAdminServiceTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;

    protected $backupConfigKeys = [
        'wp_currency.enable_scrape_foreign_rate',
        'wp_currency.enable_scrape_local_reference',
    ];

    private CurrencyService $currencyService;
    private CurrencyAdminService $currencyAdminService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;
    private LogCurrencyRevertHistoryRepository $logCurrencyRevertHistoryRepository;
    private LogCurrencyRevertHistoryPaidLogRepository $logCurrencyRevertHistoryPaidLogRepository;
    private LogCurrencyRevertHistoryFreeLogRepository $logCurrencyRevertHistoryFreeLogRepository;
    private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository;
    private AdmForeignCurrencyDailyRateRepository $admForeignCurrencyDailyRateRepository;
    private OprProductRepository $oprProductRepository;
    private MstStoreProductRepository $mstStoreProductRepository;
    private UnionLogCurrencyRepository $unionLogCurrencyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);

        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->currencyAdminService = $this->app->make(CurrencyAdminService::class);

        $this->logCurrencyRevertHistoryRepository = $this->app->make(LogCurrencyRevertHistoryRepository::class);
        $this->logCurrencyRevertHistoryPaidLogRepository = $this->app->make(LogCurrencyRevertHistoryPaidLogRepository::class);
        $this->logCurrencyRevertHistoryFreeLogRepository = $this->app->make(LogCurrencyRevertHistoryFreeLogRepository::class);
        $this->admForeignCurrencyRateRepository = $this->app->make(AdmForeignCurrencyRateRepository::class);
        $this->admForeignCurrencyDailyRateRepository = $this->app->make(AdmForeignCurrencyDailyRateRepository::class);
        $this->oprProductRepository = $this->app->make(OprProductRepository::class);
        $this->mstStoreProductRepository = $this->app->make(MstStoreProductRepository::class);
        $this->unionLogCurrencyRepository = $this->app->make(UnionLogCurrencyRepository::class);

        // テスト用に取得をオンにする
        config([
            'wp_currency.enable_scrape_foreign_rate' => true,
            'wp_currency.enable_scrape_local_reference' => true,
        ]);
    }

    public function tearDown(): void
    {
        $this->setTestNow();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('param_revertCurrencyPaidLog_返却数不正')]
    public function revertCurrencyPaidLog_返却数不正(int $revertCount)
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_PAID);
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                $revertCount
            ]
        );
    }

    public static function param_revertCurrencyPaidLog_返却数不正()
    {
        return [
            '返却数が最大返却数より大きい' => [11],
            '返却数が0' => [0],
            '返却数が-1' => [-1],
        ];
    }

    #[Test]
    #[DataProvider('param_calcRevertCount_返却数テスト')]
    public function calcRevertCount_返却数テスト(int $revertCount, int $revertAmount, bool $isError = false, bool $isPaidLog = true)
    {
        // Setup
        $expected = $revertCount - $revertAmount;

        // Exercise
        if ($isError) {
            $this->expectException(WpCurrencyException::class);
            $this->expectExceptionCode(ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_IN_REVERTING);
        }
        if ($isError && $isPaidLog) {
            $this->expectExceptionMessage("Invalid revert count. userId: 1, " .
                "logCurrencyPaidId: testLog01" .
                "revertCount: {$revertCount}" .
                "revertAmount: {$revertAmount}" .
                "newRevertCount: {$expected}");
        } elseif ($isError && !$isPaidLog) {
            $this->expectExceptionMessage("Invalid revert count. userId: 1, " .
                "logCurrencyFreeId: testLog01" .
                "revertCount: {$revertCount}" .
                "revertAmount: {$revertAmount}" .
                "newRevertCount: {$expected}");
        }
        $actual = $this->callMethod(
            $this->currencyAdminService,
            'calcRevertCount',
            [
                $revertCount,
                $revertAmount,
                '1',
                $isPaidLog,
                "testLog01",
            ]
        );
        // Verify
        if (!$isError) {
            $this->assertEquals($expected, $actual);
        }
    }

    public static function param_calcRevertCount_返却数テスト()
    {
        return [
            'revertCountがrevertAmountより大きい' => [10, 5],
            'revertCountがrevertAmountと同じ' => [10, 10],
            'revertCountがrevertAmountより小さい、paidログ' => [5, 10, true],
            'revertCountがrevertAmountより小さい、freeログ' => [5, 10, true, false],
        ];
    }

    #[Test]
    public function revertCurrencyPaidLog_有償一次通貨をログから返却する_全て返却()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                10
            ]
        );

        // Verify
        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(90, $usrCurrencySummary->paid_amount_apple);

        // 通貨の消費ログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(90, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(100, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyPaid->trigger_type);
        $this->assertEquals('', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function revertCurrencyPaidLog_有償一次通貨をログから返却する_一部返却()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                5
            ]
        );

        // Verify
        // 有償一次通貨の残高の一部が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals(95, $usrCurrencyPaid->left_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        // 後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(90, $usrCurrencySummary->paid_amount_apple);

        // 通貨の消費ログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(90, $logCurrencyPaid->before_amount);
        $this->assertEquals(5, $logCurrencyPaid->change_amount);
        $this->assertEquals(95, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyPaid->trigger_type);
        $this->assertEquals('', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function revertCurrencyPaidLog_残高レコードが複数ある場合に有償一次通貨をログから返却する_全て返却()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency 100'),
        );
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '200',
            201,
            'dummy receipt22',
            true,
            new Trigger('purchased', '1', '', 'add currency 200'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                10
            ]
        );

        // Verify
        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(290, $usrCurrencySummary->paid_amount_apple);

        // 通貨の消費ログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(100, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(290, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(300, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyPaid->trigger_type);
        $this->assertEquals('', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function revertCurrencyPaidLog_残高レコードが複数ある場合に有償一次通貨をログから返却する_一部返却()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $userCurrencyPaid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency 100'),
        );
        $userCurrencyPaidId1 = $userCurrencyPaid1->id;
        $userCurrencyPaid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '200',
            201,
            'dummy receipt22',
            true,
            new Trigger('purchased', '1', '', 'add currency 200'),
        );
        $userCurrencyPaidId2 = $userCurrencyPaid2->id;
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            250,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        // 消費時の順番がseq_no昇順なので、降順で先に来るid2の方のログを取得して渡す
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('currency_paid_id', $userCurrencyPaidId2)
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                30
            ]
        );

        // Verify
        // 有償一次通貨の残高が一部戻っていること
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId('1');
        $this->assertCount(2, $usrCurrencyPaids);
        foreach ($usrCurrencyPaids as $actual) {
            if ($actual->id === $userCurrencyPaidId1) {
                // 消費順で先の方の残高は更新されていないこと
                $this->assertEquals(0, $actual->left_amount);
            } elseif ($actual->id === $userCurrencyPaidId2) {
                // 消費順で後の方の残高に返却がされていること
                $this->assertEquals(80, $actual->left_amount);
            } else {
                $this->fail();
            }
        }

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(50, $usrCurrencySummary->paid_amount_apple);

        // 通貨の消費ログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logCurrencyPaid->billing_platform);
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals(true, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('200.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(200, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(201, $logCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $logCurrencyPaid->currency_code);
        $this->assertEquals(50, $logCurrencyPaid->before_amount);
        $this->assertEquals(30, $logCurrencyPaid->change_amount);
        $this->assertEquals(80, $logCurrencyPaid->current_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyPaid->trigger_type);
        $this->assertEquals('', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function revertCurrencyPaidLog_対象の有償一次通貨が存在しない()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        // テスト用に、有償一次通貨のレコードを削除する
        UsrCurrencyPaid::query()
            ->where('id', $log->currency_paid_id)
            ->delete();

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_PAID_CURRENCY);
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                10
            ]
        );
    }

    #[Test]
    public function revertCurrencyPaidLog_有償一次通貨に充当したら購入時の数値を超えた()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                11 // 消費個数より大きい値を返却する場合はエラーとなる
            ]
        );
    }

    #[Test]
    public function revertCurrencyPaidLog_ログと対象の有償一次通貨のseq_noが一致しない()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100);
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        $this->currencyService->usePaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        // ログのseq_noを変更する
        $log->seq_no = 999;

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::FAILED_TO_REVERT_CURRENCY_BY_NOT_MATCH_SEQ_NO);
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyPaidLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                10
            ]
        );
    }

    #[Test]
    #[DataProvider('param_revertCurrencyFreeLog_返却数不正')]
    public function revertCurrencyFreeLog_返却数不正(int $revertCount)
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 通貨の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_FREE);
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyFreeLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                $revertCount
            ]
        );
    }

    public static function param_revertCurrencyFreeLog_返却数不正()
    {
        return [
            '返却数が最大返却数より大きい' => [11],
            '返却数が0' => [0],
            '返却数が-1' => [-1],
        ];
    }

    #[Test]
    public function revertCurrencyFreeLog_無償一次通貨をログから返却する_全て返却()
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 通貨の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyFreeLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                10
            ]
        );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(320, $usrCurrencySummary->free_amount);

        // 通貨の消費ログが入っていること
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals(90, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->before_reward_amount);
        $this->assertEquals(10, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals(0, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals(0, $logCurrencyFree->change_reward_amount);
        $this->assertEquals(100, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals(110, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals(120, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals('', $logCurrencyFree->trigger_detail);
    }

    #[Test]
    #[DataProvider('param_revertCurrencyFreeLog_無償一次通貨をログから返却する_一部返却')]
    public function revertCurrencyFreeLog_無償一次通貨をログから返却する_一部返却(
        int $consumeAmount,
        int $revertAmount,
        int $expectedIngameAmount,
        int $expectedRewardAmount,
        int $expectedBonusAmount,
        int $expectedChangeIngameAmount,
        int $expectedChangeRewardAmount,
        int $expectedChangeBonusAmount,
    ) {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 通貨の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $consumeAmount,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyFreeLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                $revertAmount
            ]
        );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals($expectedIngameAmount, $usrCurrencyFree->ingame_amount);
        $this->assertEquals($expectedBonusAmount, $usrCurrencyFree->bonus_amount);
        $this->assertEquals($expectedRewardAmount, $usrCurrencyFree->reward_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(330 - $consumeAmount, $usrCurrencySummary->free_amount);

        // 通貨の消費ログが入っていること
        $expectedBeforeIngameAmount = $log['current_ingame_amount'];
        $expectedBeforeBonusAmount = $log['current_bonus_amount'];
        $expectedBeforeRewardAmount = $log['current_reward_amount'];
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals($expectedBeforeIngameAmount, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals($expectedBeforeBonusAmount, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals($expectedBeforeRewardAmount, $logCurrencyFree->before_reward_amount);
        $this->assertEquals($expectedChangeIngameAmount, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals($expectedChangeBonusAmount, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals($expectedChangeRewardAmount, $logCurrencyFree->change_reward_amount);
        $this->assertEquals($expectedIngameAmount, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals($expectedBonusAmount, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals($expectedRewardAmount, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals('', $logCurrencyFree->trigger_detail);
    }

    public static function param_revertCurrencyFreeLog_無償一次通貨をログから返却する_一部返却()
    {
        return [
            'ボーナス通貨まで消費、ボーナス通貨のみ返却' => [230, 5, 0, 0, 105, 0, 0, 5],
            'ボーナス通貨まで消費、ボーナス通貨とリワード通貨を返却' => [230, 15, 0, 5, 110, 0, 5, 10],
            'ボーナス通貨まで消費、ボーナス通貨とリワード通貨とゲーム内通貨を返却' => [230, 200, 70, 120, 110, 70, 120, 10],
            'リワード通貨まで消費、リワード通貨のみ返却' => [120, 10, 0, 110, 110, 0, 10, 0],
            'リワード通貨まで消費、リワード通貨とゲーム内通貨を返却' => [120, 30, 10, 120, 110, 10, 20, 0],
            'ゲーム内通貨のみ消費、ゲーム内通貨を返却' => [90, 10, 20, 120, 110, 10, 0, 0],
        ];
    }

    #[Test]
    #[DataProvider('param_revertCurrencyFreeLog_無償一次通貨をログから返却する_指定タイプで消費')]
    public function revertCurrencyFreeLog_無償一次通貨をログから返却する_指定タイプで消費(
        string $consumeType,
        int $consumeAmount,
        int $revertAmount,
        int $expectedIngameAmount,
        int $expectedRewardAmount,
        int $expectedBonusAmount,
        int $expectedChangeIngameAmount,
        int $expectedChangeRewardAmount,
        int $expectedChangeBonusAmount,
    ) {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 通貨の消費を指定タイプで行う
        $this->currencyService->useFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            $consumeType,
            $consumeAmount,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();

        // Exercise
        $this->callMethod(
            $this->currencyAdminService,
            'revertCurrencyFreeLog',
            [
                '1',
                $log,
                new CurrencyRevertTrigger(),
                $revertAmount
            ]
        );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals($expectedIngameAmount, $usrCurrencyFree->ingame_amount);
        $this->assertEquals($expectedBonusAmount, $usrCurrencyFree->bonus_amount);
        $this->assertEquals($expectedRewardAmount, $usrCurrencyFree->reward_amount);

        // 通貨管理の残高はこの時点では戻っていないこと
        //   後からまとめて計算するため
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(330 - $consumeAmount, $usrCurrencySummary->free_amount);

        // 通貨の消費ログが入っていること
        $expectedBeforeIngameAmount = $log['current_ingame_amount'];
        $expectedBeforeBonusAmount = $log['current_bonus_amount'];
        $expectedBeforeRewardAmount = $log['current_reward_amount'];
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyFree->os_platform);
        $this->assertEquals($expectedBeforeIngameAmount, $logCurrencyFree->before_ingame_amount);
        $this->assertEquals($expectedBeforeBonusAmount, $logCurrencyFree->before_bonus_amount);
        $this->assertEquals($expectedBeforeRewardAmount, $logCurrencyFree->before_reward_amount);
        $this->assertEquals($expectedChangeIngameAmount, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals($expectedChangeBonusAmount, $logCurrencyFree->change_bonus_amount);
        $this->assertEquals($expectedChangeRewardAmount, $logCurrencyFree->change_reward_amount);
        $this->assertEquals($expectedIngameAmount, $logCurrencyFree->current_ingame_amount);
        $this->assertEquals($expectedBonusAmount, $logCurrencyFree->current_bonus_amount);
        $this->assertEquals($expectedRewardAmount, $logCurrencyFree->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyFree->trigger_type);
        $this->assertEquals('', $logCurrencyFree->trigger_id);
        $this->assertEquals('', $logCurrencyFree->trigger_name);
        $this->assertEquals('', $logCurrencyFree->trigger_detail);
    }

    public static function param_revertCurrencyFreeLog_無償一次通貨をログから返却する_指定タイプで消費()
    {
        return [
            'ボーナス通貨タイプで消費、全返却' => [FreeCurrencyType::Bonus->value, 10, 10, 100, 120, 110, 0, 0, 10],
            'ボーナス通貨タイプで消費、一部返却' => [FreeCurrencyType::Bonus->value, 10, 5, 100, 120, 105, 0, 0, 5],
            'リワード通貨タイプで消費、全返却' => [FreeCurrencyType::Reward->value, 10, 10, 100, 120, 110, 0, 10, 0],
            'リワード通貨タイプで消費、一部返却' => [FreeCurrencyType::Reward->value, 10, 5, 100, 115, 110, 0, 5, 0],
            'ゲーム内通貨タイプで消費、全返却' => [FreeCurrencyType::Ingame->value, 10, 10, 100, 120, 110, 10, 0, 0],
            'ゲーム内通貨タイプで消費、一部返却' => [FreeCurrencyType::Ingame->value, 10, 5, 95, 120, 110, 5, 0, 0],
        ];
    }

    #[Test]
    #[DataProvider('param_revertCurrencyFromLog_返却数不正')]
    public function revertCurrencyFromLog_返却数不正(int $revertCount)
    {
        // Setup
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        // 無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            400,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaid->id,
        ];

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionCode(ErrorCode::FAILED_TO_REVERT_INVALID_REVERT_COUNT_FOR_SUM);
        $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                $revertCount,
            );
    }

    public static function param_revertCurrencyFromLog_返却数不正()
    {
        return [
            '返却数が最大返却数より大きい' => [401],
            '返却数が0' => [0],
            '返却数が-1' => [-1],
        ];
    }

    #[Test]
    public function revertCurrencyFromLog_ログから一次通貨の返却を行う_全て返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            400,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaid->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                400,
            );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);

        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);

        // 通貨管理の残高が戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(330, $usrCurrencySummary->free_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-70, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-330, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertPaidLogs->count());
        $logRevertPaidLog = $logRevertPaidLogs[0];
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaid->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaid->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 通貨の消費ログが入っていること
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertFreeLogs->count());
        $logRevertFreeLog = $logRevertFreeLogs[0];
        $this->assertEquals('1', $logRevertFreeLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertFreeLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyFree->id, $logRevertFreeLog->log_currency_free_id);
        $this->assertEquals($revertLogCurrencyFree->id, $logRevertFreeLog->revert_log_currency_free_id);
    }

    #[Test]
    public function revertCurrencyFromLog_ログから一次通貨の返却を行う_一部返却_有償のみ返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            400,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaid->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                20,
            );

        // Verify
        // 無償一次通貨の残高が戻っていないこと
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 有償一次通貨の残高が一部戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(50, $usrCurrencyPaid->left_amount);

        // 返却した有償通貨のログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(50, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);

        // 返却した無償通貨のログが入っていないこと
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertNull($logCurrencyFree);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-20, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(0, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertPaidLogs->count());
        $logRevertPaidLog = $logRevertPaidLogs[0];
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaid->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaid->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 無償通貨の消費ログが入っていないこと
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(0, $logRevertFreeLogs->count());
    }

    #[Test]
    public function revertCurrencyFromLog_ログから一次通貨の返却を行う_一部返却_有償無償の両方を返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            400,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaid->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                100,
            );

        // Verify
        // 無償一次通貨の残高が一部戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(30, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyPaid = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(30, $usrCurrencySummary->free_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-70, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-30, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertPaidLogs->count());
        $logRevertPaidLog = $logRevertPaidLogs[0];
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaid->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaid->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 通貨の消費ログが入っていること
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertFreeLogs->count());
        $logRevertFreeLog = $logRevertFreeLogs[0];
        $this->assertEquals('1', $logRevertFreeLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertFreeLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyFree->id, $logRevertFreeLog->log_currency_free_id);
        $this->assertEquals($revertLogCurrencyFree->id, $logRevertFreeLog->revert_log_currency_free_id);
    }

    #[Test]
    public function revertCurrencyFromLog_一次通貨のログが同時に複数ある場合の返却_全て返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録　(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '100',
            201,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            500,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->orderBy('seq_no', 'asc')
            ->get();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaids[0]->id,
            $revertLogCurrencyPaids[1]->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                500,
            );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertEquals(1, $logCurrencyFrees->count());
        $logCurrencyFree = $logCurrencyFrees[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);

        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid2->id);
        $this->assertEquals(200, $usrCurrencyPaid->left_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->orderBy('seq_no', 'asc')
            ->get();
        $this->assertEquals(2, $logCurrencyPaids->count());
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);

        // 通貨管理の残高が戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(300, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(330, $usrCurrencySummary->free_amount);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-170, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-330, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(2, $logRevertPaidLogs->count());
        // revertLogCurrencyPaidsとlogCurrencyPaidsはseq_no順になっているので、それで検索する
        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[0]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[0]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[0]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[1]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[1]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[1]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 返却したログと無償一次通貨ログの紐付けが入っていること
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertFreeLogs->count());
        $logRevertFreeLog = $logRevertFreeLogs[0];
        $this->assertEquals('1', $logRevertFreeLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertFreeLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyFree->id, $logRevertFreeLog->log_currency_free_id);
        $this->assertEquals($revertLogCurrencyFree->id, $logRevertFreeLog->revert_log_currency_free_id);
    }

    #[Test]
    public function revertCurrencyFromLog_一次通貨のログが同時に複数ある場合の返却_一部返却_有償のみ返却_seqnoが2のデータのみ返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録　(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '100',
            201,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            500,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->orderBy('seq_no', 'asc')
            ->get();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaids[0]->id,
            $revertLogCurrencyPaids[1]->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                10,
            );

        // Verify
        // 無償一次通貨の残高が戻っていないこと
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 返却した無償通貨のログが入っていないこと
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertEquals(0, $logCurrencyFrees->count());

        // 有償一次通貨の残高が一部戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(0, $usrCurrencyPaid->left_amount);
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid2->id);
        $this->assertEquals(140, $usrCurrencyPaid->left_amount);

        // 返却した有償通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->orderBy('seq_no', 'asc')
            ->get();
        $this->assertEquals(1, $logCurrencyPaids->count());
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals('2', $logCurrencyPaid->seq_no);
        $this->assertEquals(130, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(140, $logCurrencyPaid->current_amount);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(140, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-10, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(0, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertPaidLogs->count());
        // revertLogCurrencyPaidsとlogCurrencyPaidsはseq_no順になっているので、それで検索する
        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[0]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[0]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[1]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 無償一次通貨ログがないこと
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(0, $logRevertFreeLogs->count());
    }

    #[Test]
    public function revertCurrencyFromLog_一次通貨のログが同時に複数ある場合の返却_一部返却_有償のみ返却_両方の有償ログに返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録　(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '100',
            201,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            500,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->orderBy('seq_no', 'asc')
            ->get();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaids[0]->id,
            $revertLogCurrencyPaids[1]->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                100,
            );

        // Verify
        // 無償一次通貨の残高が戻っていないこと
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 返却した無償通貨のログが入っていないこと
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertEquals(0, $logCurrencyFrees->count());

        // 有償一次通貨の残高が一部戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(30, $usrCurrencyPaid->left_amount);
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid2->id);
        $this->assertEquals(200, $usrCurrencyPaid->left_amount);

        // 返却した有償通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->orderBy('seq_no', 'desc')
            ->get();
        $this->assertEquals(2, $logCurrencyPaids->count());
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals('2', $logCurrencyPaid->seq_no);
        $this->assertEquals(130, $logCurrencyPaid->before_amount);
        $this->assertEquals(70, $logCurrencyPaid->change_amount);
        $this->assertEquals(200, $logCurrencyPaid->current_amount);
        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals('1', $logCurrencyPaid->seq_no);
        $this->assertEquals(200, $logCurrencyPaid->before_amount);
        $this->assertEquals(30, $logCurrencyPaid->change_amount);
        $this->assertEquals(230, $logCurrencyPaid->current_amount);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(230, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-100, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(0, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(2, $logRevertPaidLogs->count());
        // revertLogCurrencyPaidsとlogCurrencyPaidsはseq_no順になっているので、それで検索する
        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[0]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[0]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[1]->id, $logRevertPaidLog->revert_log_currency_paid_id);
        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[1]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[1]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[0]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 無償一次通貨ログがないこと
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(0, $logRevertFreeLogs->count());
    }

    #[Test]
    public function revertCurrencyFromLog_一次通貨のログが同時に複数ある場合の返却_一部返却_有償無償の両方を返却()
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録　(ログも一緒)
        $paid1 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paid2 = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            200,
            'JPY',
            '100',
            201,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            500,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        $revertLogCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->orderBy('seq_no', 'asc')
            ->get();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaids[0]->id,
            $revertLogCurrencyPaids[1]->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                200,
            );

        // Verify
        // 無償一次通貨の残高が一部戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(30, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 返却した無償通貨のログが入っていること
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertEquals(1, $logCurrencyFrees->count());
        $logCurrencyFree = $logCurrencyFrees[0];
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);

        // 有償一次通貨の残高が戻っていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid1->id);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findById('1', $paid2->id);
        $this->assertEquals(200, $usrCurrencyPaid->left_amount);

        // 返却した有償通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->orderBy('seq_no', 'asc')
            ->get();
        $this->assertEquals(2, $logCurrencyPaids->count());
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(300, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(30, $usrCurrencySummary->free_amount);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-170, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-30, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(2, $logRevertPaidLogs->count());
        // revertLogCurrencyPaidsとlogCurrencyPaidsはseq_no順になっているので、それで検索する
        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[0]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[0]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[0]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        $logRevertPaidLog = $logRevertPaidLogs->where('log_currency_paid_id', $logCurrencyPaids[1]->id)->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaids[1]->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaids[1]->id, $logRevertPaidLog->revert_log_currency_paid_id);

        // 返却したログと無償一次通貨ログの紐付けが入っていること
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertFreeLogs->count());
        $logRevertFreeLog = $logRevertFreeLogs[0];
        $this->assertEquals('1', $logRevertFreeLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertFreeLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyFree->id, $logRevertFreeLog->log_currency_free_id);
        $this->assertEquals($revertLogCurrencyFree->id, $logRevertFreeLog->revert_log_currency_free_id);
    }

    #[Test]
    public function revertCurrencyFromLog_無償通貨のみ返却(): void
    {
        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        $userId = '1';
        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary($userId, 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency($userId, 100, 110, 120);
        // 通貨の消費
        $this->currencyService->useCurrency(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            10,
            new Trigger('used', '1', '', 'use currency'),
        );
        // 対象ログの取得
        $log = LogCurrencyFree::query()
            ->where('usr_user_id', $userId)
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $log->id
        ];
        $logCurrencyPaidIds = [];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog($userId, $logCurrencyPaidIds, $logCurrencyFreeIds, 'test comment', 10);

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);
        // 返却した通貨のログが入っていること
        $logCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', $userId)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->first();
        $this->assertEquals($userId, $logCurrencyFree->usr_user_id);

        // 通貨管理の残高が戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        $this->assertEquals(330, $usrCurrencySummary->free_amount);

        // 返却したログが1つ入っていること
        $this->assertEquals(1, LogCurrencyRevertHistory::query()->count());
        $logCurrencyRevertHistory = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', $userId)
            ->first();
        $this->assertEquals($userId, $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('test comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(0, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-10, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);
    }

    #[Test]
    public function revertCurrencyFromLog_複数プラットフォームにまたがって石を消費した場合に返却できるかチェック_全て返却(): void
    {
        // 現在の課金消費仕様だと、複数プラットフォームで消費することは想定されてない
        // 今回は有償通貨返却テストのため、iOSと同じTriggerでandroidの消費データを作成し消費を実行するようにしている

        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paidAppStore = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paidGooglePlay = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            200,
            'JPY',
            '200',
            0,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        //  AppStore課金の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            430,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        //  GooglePlay課金の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            200,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        //  無償通貨
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        //  有償通貨(AppStore)
        $revertLogCurrencyPaidByAppStore = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('billing_platform', CurrencyConstants::PLATFORM_APPSTORE)
            ->first();
        //  有償通貨(GooglePlay)
        $revertLogCurrencyPaidByGooglePlay = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('billing_platform', CurrencyConstants::PLATFORM_GOOGLEPLAY)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaidByAppStore->id,
            $revertLogCurrencyPaidByGooglePlay->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                630
            );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);

        // 有償一次通貨の残高が戻っていること
        //  AppStore通貨
        $usrCurrencyPaidAppStore = $this->usrCurrencyPaidRepository->findById('1', $paidAppStore->id);
        $this->assertEquals(100, $usrCurrencyPaidAppStore->left_amount);
        //  GooglePlay通貨
        $usrCurrencyPaidGooglePlay = $this->usrCurrencyPaidRepository->findById('1', $paidGooglePlay->id);
        $this->assertEquals(200, $usrCurrencyPaidGooglePlay->left_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertCount(2, $logCurrencyPaids);
        foreach ($logCurrencyPaids as $logCurrencyPaid) {
            $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        }

        // 通貨管理の残高が戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(330, $usrCurrencySummary->free_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertCount(1, $logCurrencyFrees);
        $logCurrencyFree = $logCurrencyFrees->first();
        $this->assertEquals('1', $logCurrencyFree->usr_user_id);

        // 返却したログが1つ入っていること
        $logCurrencyRevertHistories = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertCount(1, $logCurrencyRevertHistories);
        $logCurrencyRevertHistory = $logCurrencyRevertHistories->first();

        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-300, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(-330, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(2, $logRevertPaidLogs->count());
        $logCurrencyPaidByAppStore = $logCurrencyPaids->first(fn ($row) => $row->billing_platform === CurrencyConstants::PLATFORM_APPSTORE);
        $logCurrencyPaidByGooglePlay = $logCurrencyPaids->first(fn ($row) => $row->billing_platform === CurrencyConstants::PLATFORM_GOOGLEPLAY);
        //  AppStore通貨のチェック
        $logRevertPaidLogByAppStore = $logRevertPaidLogs->first(fn ($logRevertPaidLog) => $logRevertPaidLog->log_currency_paid_id === $logCurrencyPaidByAppStore->id);
        $this->assertEquals('1', $logRevertPaidLogByAppStore->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLogByAppStore->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaidByAppStore->id, $logRevertPaidLogByAppStore->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaidByAppStore->id, $logRevertPaidLogByAppStore->revert_log_currency_paid_id);
        //  GooglePlay通貨のチェック
        $logRevertPaidLogByGoogleStore = $logRevertPaidLogs->first(fn ($logRevertPaidLog) => $logRevertPaidLog->log_currency_paid_id === $logCurrencyPaidByGooglePlay->id);
        $this->assertEquals('1', $logRevertPaidLogByGoogleStore->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLogByGoogleStore->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaidByGooglePlay->id, $logRevertPaidLogByGoogleStore->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaidByGooglePlay->id, $logRevertPaidLogByGoogleStore->revert_log_currency_paid_id);

        // 通貨の消費ログが入っていること
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(1, $logRevertFreeLogs->count());
        $logRevertFreeLog = $logRevertFreeLogs->first();
        $this->assertEquals('1', $logRevertFreeLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertFreeLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyFree->id, $logRevertFreeLog->log_currency_free_id);
        $this->assertEquals($revertLogCurrencyFree->id, $logRevertFreeLog->revert_log_currency_free_id);

    }

    #[Test]
    public function revertCurrencyFromLog_複数プラットフォームにまたがって石を消費した場合に返却できるかチェック_一部返却_seqNo降順で返却できていることを確認する(): void
    {
        // 現在の課金消費仕様だと、複数プラットフォームで消費することは想定されてない
        // 今回は有償通貨返却テストのため、iOSと同じTriggerでandroidの消費データを作成し消費を実行するようにしている

        // Setup
        // 時刻を比較するため固定する
        $this->setTestNow(Carbon::create(2021, 1, 1, 0, 0, 0));

        // 通貨管理の登録
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 330);
        // 無償一次通貨の設定
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        // 有償一次通貨の登録(ログも一緒)
        $paidAppStore = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'dummy receipt 1',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        $paidGooglePlay = $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            200,
            'JPY',
            '200',
            0,
            'dummy receipt 2',
            true,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 通貨の消費
        //  無償分をすべて消費して、有償分まで使う消費数にする
        //  AppStore課金の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            430,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        //  GooglePlay課金の消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            200,
            new Trigger('used', '1', 'use name', 'use currency'),
        );
        // 対象ログの取得
        //  無償通貨
        $revertLogCurrencyFree = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', 'used')
            ->first();
        $logCurrencyFreeIds = [
            $revertLogCurrencyFree->id,
        ];
        //  有償通貨(AppStore)
        $revertLogCurrencyPaidByAppStore = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('billing_platform', CurrencyConstants::PLATFORM_APPSTORE)
            ->first();
        //  有償通貨(GooglePlay)
        $revertLogCurrencyPaidByGooglePlay = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('billing_platform', CurrencyConstants::PLATFORM_GOOGLEPLAY)
            ->first();
        $logCurrencyPaidIds = [
            $revertLogCurrencyPaidByAppStore->id,
            $revertLogCurrencyPaidByGooglePlay->id,
        ];

        // Exercise
        $revertHistoryIds = $this->currencyAdminService
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                250
            );

        // Verify
        // 無償一次通貨の残高が戻っていないこと
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // 有償一次通貨の残高が一部戻っていること
        // 消費順がiOS->Androidなので、返却はAndroid->iOSの順になる
        //  AppStore通貨
        $usrCurrencyPaidAppStore = $this->usrCurrencyPaidRepository->findById('1', $paidAppStore->id);
        $this->assertEquals(50, $usrCurrencyPaidAppStore->left_amount);
        //  GooglePlay通貨
        $usrCurrencyPaidGooglePlay = $this->usrCurrencyPaidRepository->findById('1', $paidGooglePlay->id);
        $this->assertEquals(200, $usrCurrencyPaidGooglePlay->left_amount);

        // 返却した通貨のログが入っていること
        $logCurrencyPaids = LogCurrencyPaid::query()
            ->where('usr_user_id', '1')
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->orderBy('seq_no', 'desc')
            ->get();
        $this->assertCount(2, $logCurrencyPaids);
        $logCurrencyPaid = $logCurrencyPaids[0];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals('2', $logCurrencyPaid->seq_no);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(200, $logCurrencyPaid->change_amount);
        $this->assertEquals(200, $logCurrencyPaid->current_amount);
        $logCurrencyPaid = $logCurrencyPaids[1];
        $this->assertEquals('1', $logCurrencyPaid->usr_user_id);
        $this->assertEquals('1', $logCurrencyPaid->seq_no);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(50, $logCurrencyPaid->change_amount);
        $this->assertEquals(50, $logCurrencyPaid->current_amount);

        // 通貨管理の残高が一部戻っていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals(50, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(200, $usrCurrencySummary->paid_amount_google);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);

        // 返却した無償通貨のログがないこと
        $logCurrencyFrees = LogCurrencyFree::query()
            ->where('usr_user_id', '1')
            ->where('trigger_type', Trigger::TRIGGER_TYPE_REVERT_CURRENCY)
            ->get();
        $this->assertCount(0, $logCurrencyFrees);

        // 返却したログが1つ入っていること
        $logCurrencyRevertHistories = LogCurrencyRevertHistory::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertCount(1, $logCurrencyRevertHistories);
        $logCurrencyRevertHistory = $logCurrencyRevertHistories->first();

        $this->assertEquals('1', $logCurrencyRevertHistory->usr_user_id);
        $this->assertEquals('comment', $logCurrencyRevertHistory->comment);
        $this->assertEquals('used', $logCurrencyRevertHistory->log_trigger_type);
        $this->assertEquals('1', $logCurrencyRevertHistory->log_trigger_id);
        $this->assertEquals('use name', $logCurrencyRevertHistory->log_trigger_name);
        $this->assertEquals('use currency', $logCurrencyRevertHistory->log_trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logCurrencyRevertHistory->log_request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logCurrencyRevertHistory->log_request_id);
        $this->assertEquals('2021-01-01 00:00:00', $logCurrencyRevertHistory->log_created_at);
        $this->assertEquals(-250, $logCurrencyRevertHistory->log_change_paid_amount);
        $this->assertEquals(0, $logCurrencyRevertHistory->log_change_free_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_REVERT_CURRENCY, $logCurrencyRevertHistory->trigger_type);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_id);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_name);
        $this->assertEquals('', $logCurrencyRevertHistory->trigger_detail);

        // $revertHistoryIdsと一致すること
        $this->assertCount(1, $revertHistoryIds);
        $this->assertEquals($logCurrencyRevertHistory->id, $revertHistoryIds[0]);

        // 返却したログと有償一次通貨ログの紐付けが入っていること
        $logRevertPaidLogs = LogCurrencyRevertHistoryPaidLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(2, $logRevertPaidLogs->count());
        $logCurrencyPaidByAppStore = $logCurrencyPaids->first(fn ($row) => $row->billing_platform === CurrencyConstants::PLATFORM_APPSTORE);
        $logCurrencyPaidByGooglePlay = $logCurrencyPaids->first(fn ($row) => $row->billing_platform === CurrencyConstants::PLATFORM_GOOGLEPLAY);
        //  AppStore通貨のチェック
        $logRevertPaidLogByAppStore = $logRevertPaidLogs->first(fn ($logRevertPaidLog) => $logRevertPaidLog->log_currency_paid_id === $logCurrencyPaidByAppStore->id);
        $this->assertEquals('1', $logRevertPaidLogByAppStore->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLogByAppStore->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaidByAppStore->id, $logRevertPaidLogByAppStore->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaidByAppStore->id, $logRevertPaidLogByAppStore->revert_log_currency_paid_id);
        //  GooglePlay通貨のチェック
        $logRevertPaidLogByGoogleStore = $logRevertPaidLogs->first(fn ($logRevertPaidLog) => $logRevertPaidLog->log_currency_paid_id === $logCurrencyPaidByGooglePlay->id);
        $this->assertEquals('1', $logRevertPaidLogByGoogleStore->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLogByGoogleStore->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaidByGooglePlay->id, $logRevertPaidLogByGoogleStore->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaidByGooglePlay->id, $logRevertPaidLogByGoogleStore->revert_log_currency_paid_id);

        // 通貨の消費ログが入っていないこと
        $logRevertFreeLogs = LogCurrencyRevertHistoryFreeLog::query()
            ->where('usr_user_id', '1')
            ->get();
        $this->assertEquals(0, $logRevertFreeLogs->count());
    }

    #[DataProvider('getYearOptionsData')]
    public function getYearOptions_対象期間までの年配列を取得(Carbon $now, array $expected): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        // 指定日時を現在日時として固定
        $this->setTestNow($now);

        // Exercise
        $results = $this->currencyAdminService
            ->getYearOptions();

        // Verify
        $this->assertSame($expected, $results);
    }

    #[Test]
    public function makeExcelCurrencyBalanceAggregation_正常実行(): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        // Exercise
        $excel = $this->currencyAdminService
            ->makeExcelCurrencyBalanceAggregation(
                '2023',
                '12',
                true,
                true,
                true,
                true,
                true,
                true,
                true,
                false,
            );

        // Verify
        $sheets = $excel->sheets();
        // シートが7ページ存在する
        $this->assertCount(7, $sheets);
        // 日本累計(サマリー)
        $this->assertEquals(CurrencyBalanceAggregation::class, get_class($sheets[0]));
        $this->assertEquals('日本累計(サマリー)', $sheets[0]->title());
        // 日本Apple(サマリー)
        $this->assertEquals(CurrencyBalanceAggregation::class, get_class($sheets[1]));
        $this->assertEquals('日本Apple(サマリー)', $sheets[1]->title());
        // 日本Google(サマリー)
        $this->assertEquals(CurrencyBalanceAggregation::class, get_class($sheets[2]));
        $this->assertEquals('日本Google(サマリー)', $sheets[2]->title());
        // 日本累計(内訳)
        $this->assertEquals(CurrencyPaidDetail::class, get_class($sheets[3]));
        $this->assertEquals('日本累計(内訳)', $sheets[3]->title());
        // 日本Apple(内訳)
        $this->assertEquals(CurrencyPaidDetail::class, get_class($sheets[4]));
        $this->assertEquals('日本Apple(内訳)', $sheets[4]->title());
        // 日本Google(内訳)
        $this->assertEquals(CurrencyPaidDetail::class, get_class($sheets[5]));
        $this->assertEquals('日本Google(内訳)', $sheets[5]->title());
        // 海外
        $this->assertEquals(CurrencyBalanceAggregationByForeignCountry::class, get_class($sheets[6]));
        $this->assertEquals('海外', $sheets[6]->title());

        // ファイル名が生成されていること
        $this->assertEquals('一次通貨残高集計レポート_2023-12.xlsx', $excel->getFilename());
    }

    #[Test]
    public function makeExcelCurrencyBalanceAggregation_指定したシートのみ取得(): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        // Exercise
        $excel = $this->currencyAdminService
            ->makeExcelCurrencyBalanceAggregation(
                '2023',
                '12',
                false,
                true,
                true,
                false,
                true,
                true,
                false,
                false,
            );

        // Verify
        $sheets = $excel->sheets();
        // シートが4ページ存在する
        $this->assertCount(4, $sheets);
        // 日本Apple(サマリー)
        $this->assertEquals(CurrencyBalanceAggregation::class, get_class($sheets[0]));
        $this->assertEquals('日本Apple(サマリー)', $sheets[0]->title());
        // 日本Google(サマリー)
        $this->assertEquals(CurrencyBalanceAggregation::class, get_class($sheets[1]));
        $this->assertEquals('日本Google(サマリー)', $sheets[1]->title());
        // 日本Apple(内訳)
        $this->assertEquals(CurrencyPaidDetail::class, get_class($sheets[2]));
        $this->assertEquals('日本Apple(内訳)', $sheets[2]->title());
        // 日本Google(内訳)
        $this->assertEquals(CurrencyPaidDetail::class, get_class($sheets[3]));
        $this->assertEquals('日本Google(内訳)', $sheets[3]->title());
    }

    #[Test]
    #[DataProvider('getCurrencyBalanceAggregationData')]
    public function getCurrencyBalanceAggregation_正常取得(
        bool $isIncludeSandbox,
        string $expectedSoldAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedSoldAmountMoney,
        string $expectedRemainingAmountMoney,
    ): void {
        // setUp
        // データのsetup
        $this->setupTestData();

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceAggregation = $this->currencyAdminService
            ->getCurrencyBalanceAggregation($endAt, $isIncludeSandbox, null);

        // Verify
        $excelDataArray = $currencyBalanceAggregation->collection()->toArray();
        $this->assertEquals(4, count($excelDataArray));

        $messageRow = $excelDataArray[0];
        $this->assertEquals('', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('有償通貨販売金額', $headerRow[5]);
        $this->assertEquals('有償通貨消費金額', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高', $headerRow[7]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('￥', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('￥', $unitRow[7]);

        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals($expectedSoldAmountByPaid, $dataRow[1]);
        $this->assertEquals('200', $dataRow[2]);
        $this->assertEquals('150', $dataRow[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow[4]);
        $this->assertEquals($expectedSoldAmountMoney, $dataRow[5]);
        $this->assertEquals('2000.00000000', $dataRow[6]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow[7]);
    }

    /**
     * @return array
     */
    public static function getCurrencyBalanceAggregationData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '400', '50', '4000.00000000', '500.00000000'],
            'サンドボックスデータを含める' => [true, '500', '150', '5000.00000000', '1500.00000000'],
        ];
    }

    #[Test]
    public function getCurrencyBalanceAggregation_回収ツールで回収したデータを含む(): void
    {
        // 「getCurrencyBalanceAggregation_正常取得」の「サンドボックスデータを含めない」パターンの
        // テストデータを元に実行している

        // setUp
        // データのsetup
        $this->setupTestData();
        // 回収ツールからの実行で単価ごとの内訳がマイナスとなるデータ
        //  購入
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            changeAmount: 2,
            currentAmount: 2,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  1個消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            beforeAmount: 2,
            changeAmount: -1,
            currentAmount: 1,
            triggerType: 'gacha',
            triggerId: '1-1',
            createdAtJstStr: '2023-12-03 11:15:00'
        );
        //  回収
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            beforeAmount: 1,
            changeAmount: -2,
            currentAmount: -1,
            triggerType: Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
            createdAtJstStr: '2023-12-03 11:20:00'
        );

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceAggregation = $this->currencyAdminService
            ->getCurrencyBalanceAggregation($endAt, false, null);

        // Verify
        $excelDataArray = $currencyBalanceAggregation->collection()->toArray();
        $this->assertEquals(4, count($excelDataArray));

        $messageRow = $excelDataArray[0];
        $this->assertEquals('', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('有償通貨販売金額', $headerRow[5]);
        $this->assertEquals('有償通貨消費金額', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高', $headerRow[7]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('￥', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('￥', $unitRow[7]);

        // 回収されるうちの1個を消費した結果になるかチェック
        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals('400', $dataRow[1]);
        $this->assertEquals('201', $dataRow[2]); // 消費個数 1個分加算
        $this->assertEquals('150', $dataRow[3]);
        $this->assertEquals('49', $dataRow[4]); // 残個数 1個分減算
        $this->assertEquals('4000.00000000', $dataRow[5]);
        $this->assertEquals('7000.00000000', $dataRow[6]); // 消費金額 消費分加算
        $this->assertEquals('0', $dataRow[7]); // 残高 集計結果は -3000 だがレポートは0となる
    }

    #[Test]
    #[DataProvider('getCurrencyBalanceAggregationRemainingAmountCheckData')]
    public function getCurrencyBalanceAggregation_残高の小数点比較チェック(
        int $changeAmount,
        int $currentAmount,
        string $expectedConsumeAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedConsumeAmountMoney,
        string $expectedRemainingAmountMoney
    ): void {
        // setUp
        //  購入
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            changeAmount: 1000,
            currentAmount: 1000,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 2,
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            beforeAmount: 1000,
            changeAmount: $changeAmount,
            currentAmount: $currentAmount,
            triggerType: 'gacha',
            createdAtJstStr: '2023-12-03 11:15:00'
        );

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceAggregation = $this->currencyAdminService
            ->getCurrencyBalanceAggregation($endAt, false, null);

        // Verify
        //  集計データに絞ってチェック
        $excelDataArray = $currencyBalanceAggregation->collection()->toArray();
        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals('1000', $dataRow[1]);
        $this->assertEquals($expectedConsumeAmountByPaid, $dataRow[2]);
        $this->assertEquals('0', $dataRow[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow[4]); // マイナス値の場合は0になる
        $this->assertEquals('100.00000000', $dataRow[5]);
        $this->assertEquals($expectedConsumeAmountMoney, $dataRow[6]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow[7]); // マイナス値の場合は0になる
    }

    /**
     * @return array
     */
    public static function getCurrencyBalanceAggregationRemainingAmountCheckData(): array
    {
        return [
            '残個数と残高がプラス' => [-999, 1, '999', '1', '99.90000000', '0.10000000'],
            '残個数と残高が±0' => [-1000, 0, '1000', '0', '100.00000000', '0.00000000'],
            '残個数と残高がマイナス' => [-1001, -1, '1001', '0', '100.10000000', '0'],
        ];
    }

    #[Test]
    #[DataProvider('getCurrencyBalanceAggregationPlatformData')]
    public function getCurrencyBalanceAggregation_課金プラットフォーム指定(
        string $billingPlatform,
        string $expectedSoldAmountByPaid,
        string $expectedConsumeAmountByPaid,
        string $expectedInvalidPaidAmount,
        string $expectedRemainingAmountByPaid,
        string $expectedSoldAmountMoney,
        string $expectedConsumeAmountMoney,
        string $expectedRemainingAmountMoney,
    ): void {
        // setUp
        // データのsetup
        $this->setupTestData();

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceAggregation = $this->currencyAdminService
            ->getCurrencyBalanceAggregation($endAt, true, $billingPlatform);

        // Verify
        $excelDataArray = $currencyBalanceAggregation->collection()->toArray();
        // 集計データに絞ってチェック
        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals($expectedSoldAmountByPaid, $dataRow[1]); // 有償通貨販売個数
        $this->assertEquals($expectedConsumeAmountByPaid, $dataRow[2]); // 有償通貨消費個数
        $this->assertEquals($expectedInvalidPaidAmount, $dataRow[3]); // 無効有償通貨
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow[4]); // 有償通貨残個数(有効)
        $this->assertEquals($expectedSoldAmountMoney, $dataRow[5]); // 有償通貨販売金額
        $this->assertEquals($expectedConsumeAmountMoney, $dataRow[6]); // 有償通貨消費金額
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow[7]); // 有償一次通貨残高
    }

    /**
     * @return array
     */
    public static function getCurrencyBalanceAggregationPlatformData(): array
    {
        return [
            'AppStore' => [
                CurrencyConstants::PLATFORM_APPSTORE, // billingPlatform
                '400', // 有償通貨販売個数
                '100', // 有償通貨消費個数
                '150', // 無効有償通貨
                '150', // 有償通貨残個数(有効)
                '4000.00000000', // 有償通貨販売金額
                '1000.00000000', // 有償通貨消費金額
                '1500.00000000', // 有償一次通貨残高
            ],
            'GooglePlay' => [
                CurrencyConstants::PLATFORM_GOOGLEPLAY, // billingPlatform
                '100', // 有償通貨販売個数
                '100', // 有償通貨消費個数
                '0', // 無効有償通貨
                '0', // 有償通貨残個数(有効)
                '1000.00000000', // 有償通貨販売金額
                '1000.00000000', // 有償通貨消費金額
                '0.00000000', // 有償一次通貨残高
            ],
        ];
    }

    #[Test]
    public function getCurrencyBalanceAggregation_データがない(): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        $endAt = Carbon::create(2020, 12, 31, 23, 59, 59);

        // Exercise
        $currencyBalanceAggregation = $this->currencyAdminService
            ->getCurrencyBalanceAggregation($endAt, false, 'unknown');

        // Verify
        $rows = $currencyBalanceAggregation->collection()->toArray();
        $this->assertEquals('対象データが存在しません', $rows[3][0]);
    }

    #[Test]
    #[DataProvider('getCurrencyPaidBalanceDetailData')]
    public function getCurrencyPaidBalanceDetail_正常取得(
        bool $isIncludeSandbox,
        string $expectedSoldAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedSoldAmountMoney,
        string $expectedRemainingAmountMoney
    ): void {
        // setUp
        // データのsetup
        $this->setupTestData();
        // 単価が「1」のデータを生成
        $this->makeLogCurrencyPaidRecord(
            seqNo: 5,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            purchasePrice: '100',
            purchaseAmount: 100,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00',
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 6,
            currencyPaidId: '14',
            receiptUniqueId: 'receipt_unique_id_14',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '100',
            purchaseAmount: 100,
            pricePerAmount: '1',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-02 11:00:00'
        );
        // 単価が「0」のデータを生成
        // パス商品など有償一次通貨が付与されていない場合に発生する
        $this->makeLogCurrencyPaidRecord(
            seqNo: 7,
            currencyPaidId: '15',
            receiptUniqueId: 'receipt_unique_id_15',
            purchasePrice: '1000',
            purchaseAmount: 0,
            pricePerAmount: '0',
            changeAmount: 0,
            currentAmount: 0,
            triggerType: 'shop',
            triggerId: 'shop_id_1',
            triggerName: 'shop_name_1',
            triggerDetail: 'shop_detail_1',
            createdAtJstStr: '2023-12-03 11:00:00'
        );
        // 回収ツールからの実行で単価ごとの内訳がマイナスとなるデータ
        //  購入
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            changeAmount: 2,
            currentAmount: 2,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  1個消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            beforeAmount: 2,
            changeAmount: -1,
            currentAmount: 1,
            triggerType: 'shop',
            triggerId: 'shop-1',
            createdAtJstStr: '2023-12-03 11:15:00'
        );
        //  回収
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            beforeAmount: 1,
            changeAmount: -2,
            currentAmount: -1,
            triggerType: Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
            createdAtJstStr: '2023-12-03 11:20:00'
        );

        // Exercise
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');
        $currencyPaidDetail = $this->currencyAdminService
            ->getCurrencyPaidBalanceDetail($endAt, $isIncludeSandbox, null);

        // Verify
        $excelDataArray = $currencyPaidDetail->collection()->toArray();

        // 単価0のデータは除外される
        $this->assertEquals(7, count($excelDataArray));

        $messageRow = $excelDataArray[0];
        $this->assertEquals('', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('有償通貨単価', $headerRow[5]);
        $this->assertEquals('有償通貨販売金額', $headerRow[6]);
        $this->assertEquals('有償通貨消費金額', $headerRow[7]);
        $this->assertEquals('有償一次通貨残高', $headerRow[8]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('￥', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('￥', $unitRow[7]);
        $this->assertEquals('￥', $unitRow[8]);

        $dataRow1 = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
        $this->assertEquals('100', $dataRow1[1]);
        $this->assertEquals('100', $dataRow1[2]);
        $this->assertEquals('0', $dataRow1[3]);
        $this->assertEquals('0', $dataRow1[4]);
        $this->assertEquals('1.00000000', $dataRow1[5]);
        $this->assertEquals('100.00000000', $dataRow1[6]);
        $this->assertEquals('100.00000000', $dataRow1[7]);
        $this->assertEquals('0.00000000', $dataRow1[8]);

        $dataRow2 = $excelDataArray[4];
        $this->assertEquals('リリース〜2023-12', $dataRow2[0]);
        $this->assertEquals($expectedSoldAmountByPaid, $dataRow2[1]);
        $this->assertEquals('200', $dataRow2[2]);
        $this->assertEquals('150', $dataRow2[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow2[4]);
        $this->assertEquals('10.00000000', $dataRow2[5]);
        $this->assertEquals($expectedSoldAmountMoney, $dataRow2[6]);
        $this->assertEquals('2000.00000000', $dataRow2[7]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow2[8]);

        // 回収ツールで打ち消された単価
        $dataRow3 = $excelDataArray[5];
        $this->assertEquals('リリース〜2023-12', $dataRow3[0]);
        $this->assertEquals('0', $dataRow3[1]);
        $this->assertEquals('0', $dataRow3[2]);
        $this->assertEquals('0', $dataRow3[3]);
        $this->assertEquals('0', $dataRow3[4]);
        $this->assertEquals('999.00000000', $dataRow3[5]);
        $this->assertEquals('0.00000000', $dataRow3[6]);
        $this->assertEquals('0', $dataRow3[7]);
        $this->assertEquals('0.00000000', $dataRow3[8]);

        // 回収ツール回収後マイナス表示される単価のチェック
        //  10,000円で有償通過付与個数が2個の商品を1つ購入
        //  単価は5,000円、有償通貨を1個消費、その後回収を実施
        //  回収後の販売個数、販売金額が0となる
        //  有償通貨1個分の消費が行われたので、消費個数と消費金額が加算される
        //  回収で有償通貨1個分のマイナス値になるが、最終結果では0になる
        $dataRow4 = $excelDataArray[6];
        $this->assertEquals('リリース〜2023-12', $dataRow4[0]);
        $this->assertEquals('0', $dataRow4[1]);
        $this->assertEquals('1', $dataRow4[2]);
        $this->assertEquals('0', $dataRow4[3]);
        $this->assertEquals('0', $dataRow4[4]); // 残個数 集計結果は-1だがレポートは0となる
        $this->assertEquals('5000.00000000', $dataRow4[5]);
        $this->assertEquals('0.00000000', $dataRow4[6]);
        $this->assertEquals('5000.00000000', $dataRow4[7]);
        $this->assertEquals('0', $dataRow4[8]); // 残高 集計結果は -5000 だがレポートは0となる
    }

    /**
     * @return array
     */
    public static function getCurrencyPaidBalanceDetailData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '400', '50', '4000.00000000', '500.00000000'],
            'サンドボックスデータを含める' => [true, '500', '150', '5000.00000000', '1500.00000000'],
        ];
    }

    #[Test]
    #[DataProvider('getCurrencyPaidBalanceDetailRemainingAmountCheckData')]
    public function getCurrencyPaidBalanceDetail_残高の小数点比較チェック(
        int $changeAmount,
        int $currentAmount,
        string $expectedConsumeAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedConsumeAmountMoney,
        string $expectedRemainingAmountMoney
    ): void {
        // setUp
        //  購入
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            changeAmount: 1000,
            currentAmount: 1000,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 2,
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            beforeAmount: 1000,
            changeAmount: $changeAmount,
            currentAmount: $currentAmount,
            triggerType: 'gacha',
            createdAtJstStr: '2023-12-03 11:15:00'
        );

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceDetail = $this->currencyAdminService
            ->getCurrencyPaidBalanceDetail($endAt, false, null);

        // Verify
        //  集計データに絞ってチェック
        $excelDataArray = $currencyBalanceDetail->collection()->toArray();
        $dataRow = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals('1000', $dataRow[1]);
        $this->assertEquals($expectedConsumeAmountByPaid, $dataRow[2]);
        $this->assertEquals('0', $dataRow[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow[4]); // マイナス値の場合は0になる
        $this->assertEquals('0.10000000', $dataRow[5]);
        $this->assertEquals('100.00000000', $dataRow[6]);
        $this->assertEquals($expectedConsumeAmountMoney, $dataRow[7]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow[8]); // マイナス値の場合は0になる
    }

    /**
     * @return array
     */
    public static function getCurrencyPaidBalanceDetailRemainingAmountCheckData(): array
    {
        return [
            '残個数と残高がプラス' => [-999, 1, '999', '1', '99.90000000', '0.10000000'],
            '残個数と残高が±0' => [-1000, 0, '1000', '0', '100.00000000', '0.00000000'],
            '残個数と残高がマイナス' => [-1001, -1, '1001', '0', '100.10000000', '0'],
        ];
    }

    #[Test]
    #[DataProvider('getCurrencyPaidBalanceDetailPlatformData')]
    public function getCurrencyPaidBalanceDetail_課金プラットフォーム指定(string $billingPlatform): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();
        // 単価が「1」のデータを生成
        $this->makeLogCurrencyPaidRecord(
            seqNo: 5,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            purchasePrice: '100',
            purchaseAmount: 100,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00',
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 6,
            currencyPaidId: '14',
            receiptUniqueId: 'receipt_unique_id_14',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '100',
            purchaseAmount: 100,
            pricePerAmount: '1',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-02 11:00:00'
        );

        // Exercise
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');
        $currencyPaidDetail = $this->currencyAdminService
            ->getCurrencyPaidBalanceDetail($endAt, true, $billingPlatform);

        // Verify
        $excelDataArray = $currencyPaidDetail->collection()->toArray();
        if ($billingPlatform === CurrencyConstants::PLATFORM_APPSTORE) {
            // AppleStore
            //  単価が1
            $dataRow1 = $excelDataArray[3];
            $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
            $this->assertEquals('100', $dataRow1[1]);
            $this->assertEquals('100', $dataRow1[2]);
            $this->assertEquals('0', $dataRow1[3]);
            $this->assertEquals('0', $dataRow1[4]);
            $this->assertEquals('1.00000000', $dataRow1[5]);
            $this->assertEquals('100.00000000', $dataRow1[6]);
            $this->assertEquals('100.00000000', $dataRow1[7]);
            $this->assertEquals('0.00000000', $dataRow1[8]);

            //  単価が10
            $dataRow2 = $excelDataArray[4];
            $this->assertEquals('リリース〜2023-12', $dataRow2[0]);
            $this->assertEquals('400', $dataRow2[1]);
            $this->assertEquals('100', $dataRow2[2]);
            $this->assertEquals('150', $dataRow2[3]);
            $this->assertEquals('150', $dataRow2[4]);
            $this->assertEquals('10.00000000', $dataRow2[5]);
            $this->assertEquals('4000.00000000', $dataRow2[6]);
            $this->assertEquals('1000.00000000', $dataRow2[7]);
            $this->assertEquals('1500.00000000', $dataRow2[8]);

            // 回収ツールで打ち消された単価
            $dataRow3 = $excelDataArray[5];
            $this->assertEquals('リリース〜2023-12', $dataRow3[0]);
            $this->assertEquals('0', $dataRow3[1]);
            $this->assertEquals('0', $dataRow3[2]);
            $this->assertEquals('0', $dataRow3[3]);
            $this->assertEquals('0', $dataRow3[4]);
            $this->assertEquals('999.00000000', $dataRow3[5]);
            $this->assertEquals('0.00000000', $dataRow3[6]);
            $this->assertEquals('0', $dataRow3[7]);
            $this->assertEquals('0.00000000', $dataRow3[8]);
        }

        if ($billingPlatform === CurrencyConstants::PLATFORM_GOOGLEPLAY) {
            // GooglePlay
            $dataRow1 = $excelDataArray[3];
            $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
            $this->assertEquals('100', $dataRow1[1]);
            $this->assertEquals('100', $dataRow1[2]);
            $this->assertEquals('0', $dataRow1[3]);
            $this->assertEquals('0', $dataRow1[4]);
            $this->assertEquals('10.00000000', $dataRow1[5]);
            $this->assertEquals('1000.00000000', $dataRow1[6]);
            $this->assertEquals('1000.00000000', $dataRow1[7]);
            $this->assertEquals('0.00000000', $dataRow1[8]);
        }
    }

    /**
     * @return array
     */
    public static function getCurrencyPaidBalanceDetailPlatformData(): array
    {
        return [
            'AppStore' => [CurrencyConstants::PLATFORM_APPSTORE],
            'GooglePlay' => [CurrencyConstants::PLATFORM_GOOGLEPLAY],
        ];
    }

    #[Test]
    public function getCurrencyPaidBalanceDetail_データがない(): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        $endAt = Carbon::create(2020, 12, 31, 23, 59, 59);

        // Exercise
        $currencyPaidDetail = $this->currencyAdminService
            ->getCurrencyPaidBalanceDetail($endAt, false, 'unknown');

        // Verify
        $rows = $currencyPaidDetail->collection()->toArray();
        $this->assertEquals('対象データが存在しません', $rows[3][0]);
    }

    #[Test]
    #[DataProvider('getCurrencyBalanceAggregationByForeignCountryData')]
    public function getCurrencyBalanceAggregationByForeignCountry_正常取得(
        bool $isIncludeSandbox,
        string $expectedSoldAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedRemainingAmountMoney,
        string $expectedRateCalculatedRemainingAmountMoney
    ): void {
        // setUp
        // データのsetup
        $this->setupTestData();

        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);

        // setUp
        // 通貨コードが「JPY」以外のデータを生成
        $this->makeLogCurrencyPaidRecord(
            seqNo: 5,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            purchasePrice: '120',
            purchaseAmount: 100,
            pricePerAmount: '1.20000000',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 6,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '120',
            purchaseAmount: 100,
            pricePerAmount: '1.20000000',
            currencyCode: 'USD',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: '1-1',
            createdAtJstStr: '2023-12-02 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 7,
            currencyPaidId: '14',
            receiptUniqueId: 'receipt_unique_id_14',
            purchasePrice: '100',
            purchaseAmount: 150,
            pricePerAmount: '0.66666666',
            currencyCode: 'EUR',
            changeAmount: 150,
            currentAmount: 150,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '14',
            receiptUniqueId: 'receipt_unique_id_14',
            purchasePrice: '100',
            purchaseAmount: 150,
            pricePerAmount: '0.66666666',
            currencyCode: 'EUR',
            beforeAmount: 150,
            changeAmount: 150,
            currentAmount: 300,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        // 外貨為替データにない課金情報を登録
        $this->makeLogCurrencyPaidRecord(
            userId: '200',
            currencyPaidId: '3',
            receiptUniqueId: 'receipt_unique_id_3',
            purchasePrice: '100',
            purchaseAmount: 100,
            pricePerAmount: '1',
            currencyCode: 'HKD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );

        // 外貨為替相場テーブル情報を登録
        $this->admForeignCurrencyRateRepository->insert(
            2023,
            12,
            'US Dollar',
            '米ドル',
            'USD',
            '150.58',
            '148.58'
        );
        $this->admForeignCurrencyRateRepository->insert(
            2023,
            12,
            'Euro',
            'ユーロ',
            'EUR',
            '158.62',
            '155.62'
        );

        // Exercise
        $currencyBalanceAggregationByForeignCountry = $this->currencyAdminService
            ->getCurrencyBalanceAggregationByForeignCountry($endAt, $isIncludeSandbox);

        // Verify
        $excelDataArray = $currencyBalanceAggregationByForeignCountry
            ->collection()->toArray();

        // 0行目は警告メッセージ
        $messageRow = $excelDataArray[0];
        $this->assertEquals('通貨レートが空白のデータがあります。  対象通貨: HKD', $messageRow[0]);

        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('為替コード', $headerRow[5]);
        $this->assertEquals('為替レート(月末TTM)', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高(現地通貨換算金額)', $headerRow[7]);
        $this->assertEquals('有償一次通貨残高', $headerRow[8]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('通貨コードに伴う', $unitRow[7]);
        $this->assertEquals('￥', $unitRow[8]);

        $dataRow1 = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow1[0]);
        $this->assertEquals('300', $dataRow1[1]);
        $this->assertEquals('0', $dataRow1[2]);
        $this->assertEquals('0', $dataRow1[3]);
        $this->assertEquals('300', $dataRow1[4]);
        $this->assertEquals('EUR', $dataRow1[5]);
        $this->assertEquals('157.120000', $dataRow1[6]);
        $this->assertEquals('199.99999800', $dataRow1[7]);
        $this->assertEquals('31423.99968576', $dataRow1[8]);

        $dataRow2 = $excelDataArray[4];
        $this->assertEquals('リリース〜2023-12', $dataRow2[0]);
        $this->assertEquals('100', $dataRow2[1]);
        $this->assertEquals('0', $dataRow2[2]);
        $this->assertEquals('0', $dataRow2[3]);
        $this->assertEquals('100', $dataRow2[4]);
        $this->assertEquals('HKD', $dataRow2[5]);
        $this->assertEquals('', $dataRow2[6]);
        $this->assertEquals('100.00000000', $dataRow2[7]);
        $this->assertEquals('=G5 * H5', $dataRow2[8]);

        $dataRow3 = $excelDataArray[5];
        $this->assertEquals('リリース〜2023-12', $dataRow3[0]);
        $this->assertEquals($expectedSoldAmountByPaid, $dataRow3[1]);
        $this->assertEquals('100', $dataRow3[2]);
        $this->assertEquals('0', $dataRow3[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow3[4]);
        $this->assertEquals('USD', $dataRow3[5]);
        $this->assertEquals('149.580000', $dataRow3[6]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow3[7]);
        $this->assertEquals($expectedRateCalculatedRemainingAmountMoney, $dataRow3[8]);
    }

    /**
     * @return array
     */
    public static function getCurrencyBalanceAggregationByForeignCountryData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '200', '100', '1000.00000000', '149580.00000000'],
            'サンドボックスデータを含める' => [true, '201', '101', '3000.00000000', '448740.00000000'],
        ];
    }

    #[Test]
    #[DataProvider('getCurrencyBalanceAggregationByForeignCountryRemainingAmountCheckData')]
    public function getCurrencyBalanceAggregationByForeignCountry_残高の小数点比較チェック(
        int $changeAmount,
        int $currentAmount,
        string $expectedConsumeAmountByPaid,
        string $expectedRemainingAmountByPaid,
        string $expectedRemainingAmountMoney,
        string $expectedRateCalculatedRemainingAmountMoney
    ): void {
        // setUp
        //  購入
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            currencyCode: 'USD',
            changeAmount: 1000,
            currentAmount: 1000,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 2,
            purchasePrice: '100',
            purchaseAmount: 1000,
            pricePerAmount: '0.1',
            currencyCode: 'USD',
            beforeAmount: 1000,
            changeAmount: $changeAmount,
            currentAmount: $currentAmount,
            triggerType: 'gacha',
            createdAtJstStr: '2023-12-03 11:15:00'
        );
        // 外貨為替相場テーブル情報を登録
        $this->admForeignCurrencyRateRepository->insert(
            2023,
            12,
            'US Dollar',
            '米ドル',
            'USD',
            '150.58',
            '148.58'
        );

        // 日本時間の2023-12-31 23:59:59(UTC 2023-12-31 14:59:59)
        $endAt = Carbon::parse('2023-12-31 23:59:59', CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $endAt->setTimezone('UTC');

        // Exercise
        $currencyBalanceDetail = $this->currencyAdminService
            ->getCurrencyBalanceAggregationByForeignCountry($endAt, false);

        // Verify
        //  集計データに絞ってチェック
        $excelDataArray = $currencyBalanceDetail->collection()->toArray();
        $dataRow = $excelDataArray[3];

        $this->assertEquals('リリース〜2023-12', $dataRow[0]);
        $this->assertEquals('1000', $dataRow[1]);
        $this->assertEquals($expectedConsumeAmountByPaid, $dataRow[2]);
        $this->assertEquals('0', $dataRow[3]);
        $this->assertEquals($expectedRemainingAmountByPaid, $dataRow[4]); // マイナス値の場合は0になる
        $this->assertEquals('USD', $dataRow[5]);
        $this->assertEquals('149.580000', $dataRow[6]);
        $this->assertEquals($expectedRemainingAmountMoney, $dataRow[7]); // マイナス値の場合は0になる
        $this->assertEquals($expectedRateCalculatedRemainingAmountMoney, $dataRow[8]); // マイナス値の場合は0になる
    }

    /**
     * @return array
     */
    public static function getCurrencyBalanceAggregationByForeignCountryRemainingAmountCheckData(): array
    {
        return [
            '残個数がプラス' => [-999, 1, '999', '1', '0.10000000', '14.95800000'],
            '残個数が±0' => [-1000, 0, '1000', '0', '0.00000000', '0.00000000'],
            '残個数がマイナス' => [-1001, -1, '1001', '0', '0', '0'],
        ];
    }

    #[Test]
    public function getCurrencyBalanceAggregationByForeignCountry_回収ツールで回収したデータを含む(): void
    {
        // 「getCurrencyBalanceAggregationByForeignCountry_正常取得」の「サンドボックスデータを含めない」パターンの
        // USDのテストデータを元に実行している

        // setUp
        // データのsetup
        $this->setupTestData();

        $endAt = Carbon::create(2023, 12, 31, 23, 59, 59);

        // setUp
        // 通貨コードが「USD」のデータを生成
        $this->makeLogCurrencyPaidRecord(
            seqNo: 5,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            purchasePrice: '120',
            purchaseAmount: 100,
            pricePerAmount: '1.20000000',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 6,
            currencyPaidId: '13',
            receiptUniqueId: 'receipt_unique_id_13',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '120',
            purchaseAmount: 100,
            pricePerAmount: '1.20000000',
            currencyCode: 'USD',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: 'shop',
            triggerId: 'shop-1',
            createdAtJstStr: '2023-12-02 11:00:00'
        );

        // 回収ツールからの実行で単価ごとの内訳がマイナスとなるデータ
        //  購入
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            currencyCode: 'USD',
            changeAmount: 2,
            currentAmount: 2,
            createdAtJstStr: '2023-12-03 11:10:00'
        );
        //  1個消費
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            currencyCode: 'USD',
            beforeAmount: 2,
            changeAmount: -1,
            currentAmount: 1,
            triggerType: 'shop',
            triggerId: 'shop-2',
            createdAtJstStr: '2023-12-03 11:15:00'
        );
        //  回収
        $this->makeLogCurrencyPaidRecord(
            seqNo: 8,
            currencyPaidId: '16',
            receiptUniqueId: 'receipt_unique_id_16',
            purchasePrice: '10000',
            purchaseAmount: 2,
            pricePerAmount: '5000',
            currencyCode: 'USD',
            beforeAmount: 1,
            changeAmount: -2,
            currentAmount: -1,
            triggerType: Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
            createdAtJstStr: '2023-12-03 11:20:00'
        );

        // 外貨為替相場テーブル情報を登録
        $this->admForeignCurrencyRateRepository->insert(
            2023,
            12,
            'US Dollar',
            '米ドル',
            'USD',
            '150.58',
            '148.58'
        );

        // Exercise
        $currencyBalanceAggregationByForeignCountry = $this->currencyAdminService
            ->getCurrencyBalanceAggregationByForeignCountry($endAt, false);

        // Verify
        $excelDataArray = $currencyBalanceAggregationByForeignCountry
            ->collection()->toArray();
        $headerRow = $excelDataArray[1];
        $this->assertEquals('集計期間', $headerRow[0]);
        $this->assertEquals('有償通貨販売個数', $headerRow[1]);
        $this->assertEquals('有償通貨消費個数', $headerRow[2]);
        $this->assertEquals('無効有償通貨', $headerRow[3]);
        $this->assertEquals('有償通貨残個数(有効)', $headerRow[4]);
        $this->assertEquals('為替コード', $headerRow[5]);
        $this->assertEquals('為替レート(月末TTM)', $headerRow[6]);
        $this->assertEquals('有償一次通貨残高(現地通貨換算金額)', $headerRow[7]);
        $this->assertEquals('有償一次通貨残高', $headerRow[8]);

        $unitRow = $excelDataArray[2];
        $this->assertEquals('単位', $unitRow[0]);
        $this->assertEquals('個', $unitRow[1]);
        $this->assertEquals('個', $unitRow[2]);
        $this->assertEquals('個', $unitRow[3]);
        $this->assertEquals('個', $unitRow[4]);
        $this->assertEquals('', $unitRow[5]);
        $this->assertEquals('￥', $unitRow[6]);
        $this->assertEquals('通貨コードに伴う', $unitRow[7]);
        $this->assertEquals('￥', $unitRow[8]);

        // 回収されるうちの1個を消費した結果になるかチェック
        $dataRow3 = $excelDataArray[3];
        $this->assertEquals('リリース〜2023-12', $dataRow3[0]);
        $this->assertEquals('200', $dataRow3[1]);
        $this->assertEquals('101', $dataRow3[2]); // 消費個数 1個分加算
        $this->assertEquals('0', $dataRow3[3]);
        $this->assertEquals('99', $dataRow3[4]); // 残個数 1個分減算
        $this->assertEquals('USD', $dataRow3[5]);
        $this->assertEquals('149.580000', $dataRow3[6]);
        $this->assertEquals('0', $dataRow3[7]); // 現地通貨金額残高 消費金額分減算し集計結果は -4000 だがレポートは0となる
        $this->assertEquals('0', $dataRow3[8]); // 一次通貨残高(日本円変換) 集計結果は -598320 だがレポートは0となる
    }

    #[Test]
    public function getCurrencyBalanceAggregationByForeignCountry_データがない(): void
    {
        // setUp
        // データのsetup
        $this->setupTestData();

        $endAt = Carbon::create(2020, 12, 31, 23, 59, 59);

        // Exercise
        $currencyBalanceAggregationByForeignCountry = $this->currencyAdminService
            ->getCurrencyBalanceAggregationByForeignCountry($endAt, false);

        // Verify
        $rows = $currencyBalanceAggregationByForeignCountry->collection()->toArray();
        $this->assertEquals('対象データが存在しません', $rows[3][0]);
    }

    #[Test]
    #[DataProvider('makeExcelCollaboAggregationData')]
    public function makeExcelCollaboAggregation_コラボ集計データExcelを作成(
        bool $isIncludeSandbox,
        string $expectedSumAmount,
        string $expectedCalculatedMoney
    ): void {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-12-27 00:00:00 〜 2024-01-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'JPY',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'USD',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:01+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'JPY',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-09 14:59:57+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'USD',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-09 14:59:58+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'NZD',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-09 14:59:59+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            changeAmount: -100,
            currencyCode: 'JPY',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-09 15:00:00+09:00',
        );
        // sandboxデータ
        $this->makeLogCurrencyPaidRecord(
            isSandbox: true,
            changeAmount: -99,
            currencyCode: 'JPY',
            pricePerAmount: '1',
            query: LogCurrencyPaid::QUERY_UPDATE,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:00+09:00',
        );

        // 為替レート登録
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
            [
                'id' => '2',
                'year' => '2024',
                'month' => '1',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '151.58',
                'ttb' => '149.58',
                'ttm' => '150.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $startAt = Carbon::create(2023, 12, 27, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2024, 1, 10, 23, 59, 59,  'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'shop', 'ids' => ['s-1', 's-1-2']],
        ];
        $result = $this->currencyAdminService->makeExcelCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            $isIncludeSandbox
        );

        // Verify
        $collection = $result->collection();
        $this->assertEquals(10, $collection->count());

        $rowUsd2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD' and $row[2] === '2023-12');
        $this->assertEquals('g-1', $rowUsd2312[0]);
        $this->assertEquals('USD', $rowUsd2312[1]);
        $this->assertEquals('2023-12', $rowUsd2312[2]);
        $this->assertEquals('1.00000000', $rowUsd2312[3]);
        $this->assertEquals('149.580000', $rowUsd2312[4]);
        $this->assertEquals('100', $rowUsd2312[5]);
        $this->assertEquals('14958.00000000', $rowUsd2312[6]);

        $rowUsd2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowUsd2401[0]);
        $this->assertEquals('USD', $rowUsd2401[1]);
        $this->assertEquals('2024-01', $rowUsd2401[2]);
        $this->assertEquals('1.00000000', $rowUsd2401[3]);
        $this->assertEquals('150.580000', $rowUsd2401[4]);
        $this->assertEquals('100', $rowUsd2401[5]);
        $this->assertEquals('15058.00000000', $rowUsd2401[6]);

        $rowJpy2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2023-12');
        $this->assertEquals('g-1', $rowJpy2312[0]);
        $this->assertEquals('JPY', $rowJpy2312[1]);
        $this->assertEquals('2023-12', $rowJpy2312[2]);
        $this->assertEquals('1.00000000', $rowJpy2312[3]);
        $this->assertEquals('1', $rowJpy2312[4]);
        $this->assertEquals($expectedSumAmount, $rowJpy2312[5]);
        $this->assertEquals($expectedCalculatedMoney, $rowJpy2312[6]);

        $rowJpy2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowJpy2401[0]);
        $this->assertEquals('JPY', $rowJpy2401[1]);
        $this->assertEquals('2024-01', $rowJpy2401[2]);
        $this->assertEquals('1.00000000', $rowJpy2401[3]);
        $this->assertEquals('1', $rowJpy2401[4]);
        $this->assertEquals('200', $rowJpy2401[5]);
        $this->assertEquals('200.00000000', $rowJpy2401[6]);

        // collectionからNZDに合致する最初のレコードとその配列のキーを取得する
        $nzdCollection = $collection->filter(fn ($row) => isset($row[1]) && $row[1] === 'NZD');
        $rowNzd = $nzdCollection->first();
        $key = $nzdCollection->keys()->first();
        $row = $key + 1;
        $this->assertEquals('g-1', $rowNzd[0]);
        $this->assertEquals('NZD', $rowNzd[1]);
        $this->assertEquals('2024-01', $rowNzd[2]);
        $this->assertEquals('1.00000000', $rowNzd[3]);
        $this->assertEquals('', $rowNzd[4]);
        $this->assertEquals('100', $rowNzd[5]);
        $this->assertEquals("=C{$row} * D{$row} * E{$row}", $rowNzd[6]);
    }

    /**
     * @return array
     */
    public static function makeExcelCollaboAggregationData(): array
    {
        return [
            'サンドボックスデータを含めない' => [false, '100', '100.00000000'],
            'サンドボックスデータを含める' => [true, '199', '199.00000000'],
        ];
    }

    #[Test]
    public function makeExcelCollaboAggregation_一次通貨返却で返却されたデータが除外されて集計される_全返却のみ(): void
    {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1
        //   コラボ期間(JST): 2023-12-27 00:00:00 〜 2024-01-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:01+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-02 00:00:00+09:00',
        );
        // 消費した後に一次通貨返却が行われた
        //  消費データ(返却対象)
        $revertTargetLodId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 00:00:00+09:00',
        );
        $revertTargetLodId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1-2',
            triggerName: 'コラボ2',
            createdAtJstStr: '2024-01-03 00:30:00+09:00',
        );
        $revertTargetLodId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 01:00:01+09:00',
        );
        //  一次通貨返却で生成されたデータ(返却対象)
        // 全返却
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:00:00+09:00',
        );
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:30:00+09:00',
        );
        $revertLogId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 01:00:00+09:00',
        );
        // 有償一次通貨返却履歴テーブルに登録
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '1',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId1',
                    'log_currency_paid_id' => $revertLogId1,
                    'revert_log_currency_paid_id' => $revertTargetLodId1,
                    'created_at' => '2024-01-07 00:00:00+09:00',
                    'updated_at' => '2024-01-07 00:00:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '2',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId2',
                    'log_currency_paid_id' => $revertLogId2,
                    'revert_log_currency_paid_id' => $revertTargetLodId2,
                    'created_at' => '2024-01-07 00:30:00+09:00',
                    'updated_at' => '2024-01-07 00:30:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '3',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId3',
                    'log_currency_paid_id' => $revertLogId3,
                    'revert_log_currency_paid_id' => $revertTargetLodId3,
                    'created_at' => '2024-01-07 01:00:00+09:00',
                    'updated_at' => '2024-01-07 01:00:00+09:00',
                ],
            );

        // 為替レート登録
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
            [
                'id' => '2',
                'year' => '2024',
                'month' => '1',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '151.58',
                'ttb' => '149.58',
                'ttm' => '150.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $startAt = Carbon::create(2023, 12, 27, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2024, 1, 10, 23, 59, 59,  'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'shop', 'ids' => ['s-1', 's-1-2']],
        ];
        $result = $this->currencyAdminService->makeExcelCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            false
        );

        // Verify
        $collection = $result->collection();
        $this->assertEquals(8, $collection->count());

        $rowUsd2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD');
        $this->assertEquals('g-1', $rowUsd2312[0]);
        $this->assertEquals('USD', $rowUsd2312[1]);
        $this->assertEquals('2023-12', $rowUsd2312[2]);
        $this->assertEquals('1.00000000', $rowUsd2312[3]);
        $this->assertEquals('149.580000', $rowUsd2312[4]);
        $this->assertEquals('100', $rowUsd2312[5]);
        $this->assertEquals('14958.00000000', $rowUsd2312[6]);

        $rowJpy2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2023-12');
        $this->assertEquals('g-1', $rowJpy2312[0]);
        $this->assertEquals('JPY', $rowJpy2312[1]);
        $this->assertEquals('2023-12', $rowJpy2312[2]);
        $this->assertEquals('1.00000000', $rowJpy2312[3]);
        $this->assertEquals('1', $rowJpy2312[4]);
        $this->assertEquals('100', $rowJpy2312[5]);
        $this->assertEquals('100.00000000', $rowJpy2312[6]);

        // 一次通貨の全返却が実行された分は含まれてない
        $rowJpy2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowJpy2401[0]);
        $this->assertEquals('JPY', $rowJpy2401[1]);
        $this->assertEquals('2024-01', $rowJpy2401[2]);
        $this->assertEquals('1.00000000', $rowJpy2401[3]);
        $this->assertEquals('1', $rowJpy2401[4]);
        $this->assertEquals('100', $rowJpy2401[5]);
        $this->assertEquals('100.00000000', $rowJpy2401[6]);

        // 一次通貨返却が実行され消費データない集計結果になる
        $rowGacha2 = $collection->first(fn ($row) => isset($row[1]) && $row[0] === 'g-1-2');
        $this->assertEquals('g-1-2', $rowGacha2[0]);
        $this->assertEquals('-', $rowGacha2[1]);
        $this->assertEquals('-', $rowGacha2[2]);
        $this->assertEquals('-', $rowGacha2[3]);
        $this->assertEquals('-', $rowGacha2[4]);
        $this->assertEquals('-', $rowGacha2[5]);
        $this->assertEquals('-', $rowGacha2[6]);
    }

    #[Test]
    public function makeExcelCollaboAggregation_一次通貨返却で返却されたデータが除外されて集計される_一部返却のみ(): void
    {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1
        //   コラボ期間(JST): 2023-12-27 00:00:00 〜 2024-01-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:01+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-02 00:00:00+09:00',
        );
        // 消費した後に一次通貨返却が行われた
        //  消費データ(返却対象)
        $revertTargetLodId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 00:00:00+09:00',
        );
        $revertTargetLodId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1-2',
            triggerName: 'コラボ2',
            createdAtJstStr: '2024-01-03 00:30:00+09:00',
        );
        $revertTargetLodId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 01:00:01+09:00',
        );
        //  一次通貨返却で生成されたデータ(返却対象)
        // 一部返却
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 90,
            currentAmount: 90,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:00:00+09:00',
        );
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 50,
            currentAmount: 50,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:30:00+09:00',
        );
        $revertLogId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 10,
            currentAmount: 10,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 01:00:00+09:00',
        );
        // 有償一次通貨返却履歴テーブルに登録
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '1',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId1',
                    'log_currency_paid_id' => $revertLogId1,
                    'revert_log_currency_paid_id' => $revertTargetLodId1,
                    'created_at' => '2024-01-07 00:00:00+09:00',
                    'updated_at' => '2024-01-07 00:00:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '2',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId2',
                    'log_currency_paid_id' => $revertLogId2,
                    'revert_log_currency_paid_id' => $revertTargetLodId2,
                    'created_at' => '2024-01-07 00:30:00+09:00',
                    'updated_at' => '2024-01-07 00:30:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '3',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId3',
                    'log_currency_paid_id' => $revertLogId3,
                    'revert_log_currency_paid_id' => $revertTargetLodId3,
                    'created_at' => '2024-01-07 01:00:00+09:00',
                    'updated_at' => '2024-01-07 01:00:00+09:00',
                ],
            );

        // 為替レート登録
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
            [
                'id' => '2',
                'year' => '2024',
                'month' => '1',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '151.58',
                'ttb' => '149.58',
                'ttm' => '150.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $startAt = Carbon::create(2023, 12, 27, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2024, 1, 10, 23, 59, 59,  'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'shop', 'ids' => ['s-1', 's-1-2']],
        ];
        $result = $this->currencyAdminService->makeExcelCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            false
        );

        // Verify
        $collection = $result->collection();
        $this->assertEquals(9, $collection->count());

        // gacha_id/product_id と 消費年月 でソート
        $collection = $collection->sortBy(fn ($row) => [$row[0] ?? '', $row[2] ?? '']);

        $rowUsd2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD');
        $this->assertEquals('g-1', $rowUsd2312[0]);
        $this->assertEquals('USD', $rowUsd2312[1]);
        $this->assertEquals('2023-12', $rowUsd2312[2]);
        $this->assertEquals('1.00000000', $rowUsd2312[3]);
        $this->assertEquals('149.580000', $rowUsd2312[4]);
        $this->assertEquals('100', $rowUsd2312[5]);
        $this->assertEquals('14958.00000000', $rowUsd2312[6]);

        $rowJpy2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2023-12');
        $this->assertEquals('g-1', $rowJpy2312[0]);
        $this->assertEquals('JPY', $rowJpy2312[1]);
        $this->assertEquals('2023-12', $rowJpy2312[2]);
        $this->assertEquals('1.00000000', $rowJpy2312[3]);
        $this->assertEquals('1', $rowJpy2312[4]);
        $this->assertEquals('100', $rowJpy2312[5]);
        $this->assertEquals('100.00000000', $rowJpy2312[6]);

        // 一次通貨の一部返却分だけ減っている
        $rowJpy2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowJpy2401[0]);
        $this->assertEquals('JPY', $rowJpy2401[1]);
        $this->assertEquals('2024-01', $rowJpy2401[2]);
        $this->assertEquals('1.00000000', $rowJpy2401[3]);
        $this->assertEquals('1', $rowJpy2401[4]);
        $this->assertEquals('110', $rowJpy2401[5]);
        $this->assertEquals('110.00000000', $rowJpy2401[6]);

        // 一次通貨の一部返却が実行された
        $rowUsd2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowUsd2401[0]);
        $this->assertEquals('USD', $rowUsd2401[1]);
        $this->assertEquals('2024-01', $rowUsd2401[2]);
        $this->assertEquals('1.00000000', $rowUsd2401[3]);
        $this->assertEquals('150.580000', $rowUsd2401[4]);
        $this->assertEquals('90', $rowUsd2401[5]);
        $this->assertEquals('13552.20000000', $rowUsd2401[6]);

        $rowGacha2 = $collection->first(fn ($row) => isset($row[1]) && $row[0] === 'g-1-2');
        $this->assertEquals('g-1-2', $rowGacha2[0]);
        $this->assertEquals('JPY', $rowGacha2[1]);
        $this->assertEquals('2024-01', $rowGacha2[2]);
        $this->assertEquals('1.00000000', $rowGacha2[3]);
        $this->assertEquals('1', $rowGacha2[4]);
        $this->assertEquals('50', $rowGacha2[5]);
        $this->assertEquals('50.00000000', $rowGacha2[6]);
    }

    #[Test]
    public function makeExcelCollaboAggregation_一次通貨返却で返却されたデータが除外されて集計される_全返却と一部返却の混合(): void
    {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1
        //   コラボ期間(JST): 2023-12-27 00:00:00 〜 2024-01-10 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-12-27 00:00:01+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-02 00:00:00+09:00',
        );
        // 消費した後に一次通貨返却が行われた
        //  消費データ(返却対象)
        $revertTargetLodId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 00:00:00+09:00',
        );
        $revertTargetLodId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1-2',
            triggerName: 'コラボ2',
            createdAtJstStr: '2024-01-03 00:30:00+09:00',
        );
        $revertTargetLodId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2024-01-03 01:00:01+09:00',
        );
        $revertTargetLodId4 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1-3',
            triggerName: 'コラボ2',
            createdAtJstStr: '2023-12-30 00:30:00+09:00',
        );
        //  一次通貨返却で生成されたデータ(返却対象)
        // 全返却
        $revertLogId1 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:00:00+09:00',
        );
        $revertLogId4 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 100,
            currentAmount: 100,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:30:00+09:00',
        );
        // 一部返却
        $revertLogId2 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 50,
            currentAmount: 50,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 00:30:00+09:00',
        );
        $revertLogId3 = $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            changeAmount: 10,
            currentAmount: 10,
            triggerType: 'revert_currency',
            createdAtJstStr: '2024-01-07 01:00:00+09:00',
        );
        // 有償一次通貨返却履歴テーブルに登録
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '1',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId1',
                    'log_currency_paid_id' => $revertLogId1,
                    'revert_log_currency_paid_id' => $revertTargetLodId1,
                    'created_at' => '2024-01-07 00:00:00+09:00',
                    'updated_at' => '2024-01-07 00:00:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '2',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId2',
                    'log_currency_paid_id' => $revertLogId2,
                    'revert_log_currency_paid_id' => $revertTargetLodId2,
                    'created_at' => '2024-01-07 00:30:00+09:00',
                    'updated_at' => '2024-01-07 00:30:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '3',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId3',
                    'log_currency_paid_id' => $revertLogId3,
                    'revert_log_currency_paid_id' => $revertTargetLodId3,
                    'created_at' => '2024-01-07 01:00:00+09:00',
                    'updated_at' => '2024-01-07 01:00:00+09:00',
                ],
            );
        LogCurrencyRevertHistoryPaidLog::query()
            ->insert(
                [
                    'id' => '4',
                    'usr_user_id' => '100',
                    'log_currency_revert_history_id' => 'logCurrencyRevertHistoryId4',
                    'log_currency_paid_id' => $revertLogId4,
                    'revert_log_currency_paid_id' => $revertTargetLodId4,
                    'created_at' => '2024-01-07 01:00:00+09:00',
                    'updated_at' => '2024-01-07 01:00:00+09:00',
                ],
            );

        // 為替レート登録
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
            [
                'id' => '2',
                'year' => '2024',
                'month' => '1',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '151.58',
                'ttb' => '149.58',
                'ttm' => '150.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);

        // Exercise
        $startAt = Carbon::create(2023, 12, 27, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2024, 1, 10, 23, 59, 59,  'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2', 'g-1-3']],
            ['type' => 'shop', 'ids' => ['s-1', 's-1-2']],
        ];
        $result = $this->currencyAdminService->makeExcelCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            false
        );

        // Verify
        $collection = $result->collection();
        $this->assertEquals(10, $collection->count());

        // gacha_id/product_id と 消費年月 でソート
        $collection = $collection->sortBy(fn ($row) => [$row[0] ?? '', $row[2] ?? '']);

        $rowUsd2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD');
        $this->assertEquals('g-1', $rowUsd2312[0]);
        $this->assertEquals('USD', $rowUsd2312[1]);
        $this->assertEquals('2023-12', $rowUsd2312[2]);
        $this->assertEquals('1.00000000', $rowUsd2312[3]);
        $this->assertEquals('149.580000', $rowUsd2312[4]);
        $this->assertEquals('100', $rowUsd2312[5]);
        $this->assertEquals('14958.00000000', $rowUsd2312[6]);

        $rowJpy2312 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2023-12');
        $this->assertEquals('g-1', $rowJpy2312[0]);
        $this->assertEquals('JPY', $rowJpy2312[1]);
        $this->assertEquals('2023-12', $rowJpy2312[2]);
        $this->assertEquals('1.00000000', $rowJpy2312[3]);
        $this->assertEquals('1', $rowJpy2312[4]);
        $this->assertEquals('100', $rowJpy2312[5]);
        $this->assertEquals('100.00000000', $rowJpy2312[6]);

        // 一次通貨の全返却が実行された分は含まれてない
        $rowJpy2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowJpy2401[0]);
        $this->assertEquals('JPY', $rowJpy2401[1]);
        $this->assertEquals('2024-01', $rowJpy2401[2]);
        $this->assertEquals('1.00000000', $rowJpy2401[3]);
        $this->assertEquals('1', $rowJpy2401[4]);
        $this->assertEquals('100', $rowJpy2401[5]);
        $this->assertEquals('100.00000000', $rowJpy2401[6]);

        // 一次通貨の一部返却が実行された
        $rowUsd2401 = $collection->first(fn ($row) => isset($row[1]) && $row[1] === 'USD' and $row[2] === '2024-01');
        $this->assertEquals('g-1', $rowUsd2401[0]);
        $this->assertEquals('USD', $rowUsd2401[1]);
        $this->assertEquals('2024-01', $rowUsd2401[2]);
        $this->assertEquals('1.00000000', $rowUsd2401[3]);
        $this->assertEquals('150.580000', $rowUsd2401[4]);
        $this->assertEquals('90', $rowUsd2401[5]);
        $this->assertEquals('13552.20000000', $rowUsd2401[6]);

        // 一次通貨返却が実行される(一部返却)
        $rowGacha2 = $collection->first(fn ($row) => isset($row[1]) && $row[0] === 'g-1-2');
        $this->assertEquals('g-1-2', $rowGacha2[0]);
        $this->assertEquals('JPY', $rowGacha2[1]);
        $this->assertEquals('2024-01', $rowGacha2[2]);
        $this->assertEquals('1.00000000', $rowGacha2[3]);
        $this->assertEquals('1', $rowGacha2[4]);
        $this->assertEquals('50', $rowGacha2[5]);
        $this->assertEquals('50.00000000', $rowGacha2[6]);

        // 一次通貨返却(全返却)が実行され消費データない集計結果になる
        $rowGacha3 = $collection->first(fn ($row) => isset($row[1]) && $row[0] === 'g-1-3');
        $this->assertEquals('g-1-3', $rowGacha3[0]);
        $this->assertEquals('-', $rowGacha3[1]);
        $this->assertEquals('-', $rowGacha3[2]);
        $this->assertEquals('-', $rowGacha3[3]);
        $this->assertEquals('-', $rowGacha3[4]);
        $this->assertEquals('-', $rowGacha3[5]);
        $this->assertEquals('-', $rowGacha3[6]);
    }

    #[Test]
    public function getAdmForeignCurrencyRateCollection_データ取得(): void
    {
        // Setup
        $this->makeAdmForeignCurrencyRate();

        // Exercise
        $collection = $this->currencyAdminService
            ->getAdmForeignCurrencyRateCollection(2023, 12);
        /** @var AdmForeignCurrencyRate $result */
        $result = $collection->first();

        // Verify
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(2023, $result->year);
        $this->assertEquals(12, $result->month);
        $this->assertEquals('US Dollar', $result->currency);
        $this->assertEquals('米ドル', $result->currency_name);
        $this->assertEquals('USD', $result->currency_code);
        $this->assertEquals('150.580000', $result->tts);
        $this->assertEquals('148.580000', $result->ttb);
        $this->assertEquals('149.580000', $result->ttm);
    }

    #[Test]
    public function getAdmForeignCurrencyRateCollection_データが空(): void
    {
        // Setup
        $this->makeAdmForeignCurrencyRate();

        // Exercise
        $collection = $this->currencyAdminService
            ->getAdmForeignCurrencyRateCollection(2023, 10);

        // Verify
        $this->assertTrue($collection->isEmpty());
    }

    #[Test]
    #[DataProvider('existsScrapeForeignCurrencyRateData')]
    public function existsScrapeForeignCurrencyRateByYearAndMonth_チェック実行(int $year, int $month, bool $expected1, bool $expected2, bool $invalidYearAndMonth = false): void
    {
        // Setup
        $insertData = collect();
        if ($expected1) {
            $insertData = $this->createInsertDataForParseForeignRate($year, $month);
        }
        if ($expected2) {
            $insertData2 = $this->createInsertDataForParseLocalReference($year, $month);
        }
        if ($expected1 && $expected2) {
            $insertData = $insertData->merge($insertData2);
        } elseif (!$expected1 && $expected2) {
            $insertData = $insertData2;
        }
        AdmForeignCurrencyRate::query()->insert($insertData->toArray());

        // Exercise
        if ($invalidYearAndMonth) {
            $year = 2024;
            $month = 10;
        }
        $exists = $this->currencyAdminService
            ->existsScrapeForeignCurrencyRateByYearAndMonth($year, $month);

        // Verify
        $this->assertEquals($expected1, $exists['existForeignRate']);
        $this->assertEquals($expected2, $exists['existLocalReference']);
    }

    /**
     * @return array
     */
    public static function existsScrapeForeignCurrencyRateData(): array
    {
        return [
            '存在する' => [2023, 12, true, true],
            '存在しない' => [2023, 12, false, false],
            '存在しない_存在しない年月' => [2023, 12, false, false, true],
            'USDは存在する' => [2023, 12, true, false],
            'TWDは存在する' => [2023, 12, false, true],
        ];
    }


    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD未取得_TWD未取得(): void
    {
        // Setup
        $resultCollection = collect(
            [
                [
                    'currency' => 'US Dollar',
                    'currencyName' => '米ドル',
                    'currencyCode' => 'USD',
                    'tts' => '142.83',
                    'ttb' => '140.83',
                ],
                [
                    'currency' => 'Euro',
                    'currencyName' => 'ユーロ',
                    'currencyCode' => 'EUR',
                    'tts' => '158.62',
                    'ttb' => '155.62',
                ],
                [
                    'currency' => 'Canadian Dollar',
                    'currencyName' => 'カナダ・ドル',
                    'currencyCode' => 'CAD',
                    'tts' => '108.84',
                    'ttb' => '105.64',
                ],
            ]
        );
        $resultCollection2 = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currencyName' => 'マレーシア・リンギット',
                    'currencyCode' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );
        $year = 2023;
        $month = 12;

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection2);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 5件登録されていることをチェック
        $this->assertEquals(5, $result->count());

        // USD チェック
        $resultUsd = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'USD');
        $this->assertEquals('2023', $resultUsd->year);
        $this->assertEquals('12', $resultUsd->month);
        $this->assertEquals('USD', $resultUsd->currency_code);
        $this->assertEquals('US Dollar', $resultUsd->currency);
        $this->assertEquals('米ドル', $resultUsd->currency_name);
        $this->assertEquals('142.830000', $resultUsd->tts);
        $this->assertEquals('140.830000', $resultUsd->ttb);
        $this->assertEquals('141.830000', $resultUsd->ttm);

        // EUR チェック
        $resultEur = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'EUR');
        $this->assertEquals('2023', $resultEur->year);
        $this->assertEquals('12', $resultEur->month);
        $this->assertEquals('EUR', $resultEur->currency_code);
        $this->assertEquals('Euro', $resultEur->currency);
        $this->assertEquals('ユーロ', $resultEur->currency_name);
        $this->assertEquals('158.620000', $resultEur->tts);
        $this->assertEquals('155.620000', $resultEur->ttb);
        $this->assertEquals('157.120000', $resultEur->ttm);

        // CAD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'CAD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('CAD', $resultCad->currency_code);
        $this->assertEquals('Canadian Dollar', $resultCad->currency);
        $this->assertEquals('カナダ・ドル', $resultCad->currency_name);
        $this->assertEquals('108.840000', $resultCad->tts);
        $this->assertEquals('105.640000', $resultCad->ttb);
        $this->assertEquals('107.240000', $resultCad->ttm);

        // TWD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'TWD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('TWD', $resultCad->currency_code);
        $this->assertEquals('New Taiwan Dollar', $resultCad->currency);
        $this->assertEquals('台湾ドル', $resultCad->currency_name);
        $this->assertEquals('0.218900', $resultCad->tts);
        $this->assertEquals('0.214900', $resultCad->ttb);
        $this->assertEquals('0.216900', $resultCad->ttm);

        // MYR チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'MYR');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('MYR', $resultCad->currency_code);
        $this->assertEquals('Malaysian Ringgit', $resultCad->currency);
        $this->assertEquals('マレーシア・リンギット', $resultCad->currency_name);
        $this->assertEquals('3.303000', $resultCad->tts);
        $this->assertEquals('3.183000', $resultCad->ttb);
        $this->assertEquals('3.243000', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD全未取得_TWD取得(): void
    {
        // Setup
        $year = 2023;
        $month = 12;
        $resultCollection = collect(
            [
                [
                    'currency' => 'US Dollar',
                    'currencyName' => '米ドル',
                    'currencyCode' => 'USD',
                    'tts' => '142.83',
                    'ttb' => '140.83',
                ],
                [
                    'currency' => 'Euro',
                    'currencyName' => 'ユーロ',
                    'currencyCode' => 'EUR',
                    'tts' => '158.62',
                    'ttb' => '155.62',
                ],
                [
                    'currency' => 'Canadian Dollar',
                    'currencyName' => 'カナダ・ドル',
                    'currencyCode' => 'CAD',
                    'tts' => '108.84',
                    'ttb' => '105.64',
                ],
            ]
        );
        $insertData = collect(
            [
                [
                    'year' => $year,
                    'month' => $month,
                    'currency' => 'New Taiwan Dollar',
                    'currency_name' => '台湾ドル',
                    'currency_code' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'year' => $year,
                    'month' => $month,
                    'currency' => 'Malaysian Ringgit',
                    'currency_name' => 'マレーシア・リンギット',
                    'currency_code' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($insertData);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)
            ->willReturn($insertData);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 3件登録されていることをチェック(TWD,MYRはモックで設定しているためDBにない)
        $this->assertEquals(3, $result->count());

        // USD チェック
        $resultUsd = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'USD');
        $this->assertEquals('2023', $resultUsd->year);
        $this->assertEquals('12', $resultUsd->month);
        $this->assertEquals('USD', $resultUsd->currency_code);
        $this->assertEquals('US Dollar', $resultUsd->currency);
        $this->assertEquals('米ドル', $resultUsd->currency_name);
        $this->assertEquals('142.830000', $resultUsd->tts);
        $this->assertEquals('140.830000', $resultUsd->ttb);
        $this->assertEquals('141.830000', $resultUsd->ttm);

        // EUR チェック
        $resultEur = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'EUR');
        $this->assertEquals('2023', $resultEur->year);
        $this->assertEquals('12', $resultEur->month);
        $this->assertEquals('EUR', $resultEur->currency_code);
        $this->assertEquals('Euro', $resultEur->currency);
        $this->assertEquals('ユーロ', $resultEur->currency_name);
        $this->assertEquals('158.620000', $resultEur->tts);
        $this->assertEquals('155.620000', $resultEur->ttb);
        $this->assertEquals('157.120000', $resultEur->ttm);

        // CAD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'CAD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('CAD', $resultCad->currency_code);
        $this->assertEquals('Canadian Dollar', $resultCad->currency);
        $this->assertEquals('カナダ・ドル', $resultCad->currency_name);
        $this->assertEquals('108.840000', $resultCad->tts);
        $this->assertEquals('105.640000', $resultCad->ttb);
        $this->assertEquals('107.240000', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD一部未取得_TWD取得(): void
    {
        // Setup
        $year = 2023;
        $month = 12;
        $resultCollection = collect(
            [
                [
                    'currency' => 'US Dollar',
                    'currencyName' => '米ドル',
                    'currencyCode' => 'USD',
                    'tts' => '142.83',
                    'ttb' => '140.83',
                ],
                [
                    'currency' => 'Euro',
                    'currencyName' => 'ユーロ',
                    'currencyCode' => 'EUR',
                    'tts' => '158.62',
                    'ttb' => '155.62',
                ],
                [
                    'currency' => 'Canadian Dollar',
                    'currencyName' => 'カナダ・ドル',
                    'currencyCode' => 'CAD',
                    'tts' => '108.84',
                    'ttb' => '105.64',
                ],
            ]
        );
        $insertData = collect(
            [
                [
                    'year' => $year,
                    'month' => $month,
                    'currency' => 'Yuan Renminbi',
                    'currency_name' => '中国・人民元',
                    'currency_code' => 'CNY',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'year' => $year,
                    'month' => $month,
                    'currency' => 'New Taiwan Dollar',
                    'currency_name' => '台湾ドル',
                    'currency_code' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'year' => $year,
                    'month' => $month,
                    'currency' => 'Malaysian Ringgit',
                    'currency_name' => 'マレーシア・リンギット',
                    'currency_code' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($insertData);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)
            ->willReturn($insertData);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 3件登録されていることをチェック(TWD,MYRはモックで設定しているためDBにない)
        $this->assertEquals(3, $result->count());

        // USD チェック
        $resultUsd = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'USD');
        $this->assertEquals('2023', $resultUsd->year);
        $this->assertEquals('12', $resultUsd->month);
        $this->assertEquals('USD', $resultUsd->currency_code);
        $this->assertEquals('US Dollar', $resultUsd->currency);
        $this->assertEquals('米ドル', $resultUsd->currency_name);
        $this->assertEquals('142.830000', $resultUsd->tts);
        $this->assertEquals('140.830000', $resultUsd->ttb);
        $this->assertEquals('141.830000', $resultUsd->ttm);

        // EUR チェック
        $resultEur = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'EUR');
        $this->assertEquals('2023', $resultEur->year);
        $this->assertEquals('12', $resultEur->month);
        $this->assertEquals('EUR', $resultEur->currency_code);
        $this->assertEquals('Euro', $resultEur->currency);
        $this->assertEquals('ユーロ', $resultEur->currency_name);
        $this->assertEquals('158.620000', $resultEur->tts);
        $this->assertEquals('155.620000', $resultEur->ttb);
        $this->assertEquals('157.120000', $resultEur->ttm);

        // CAD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'CAD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('CAD', $resultCad->currency_code);
        $this->assertEquals('Canadian Dollar', $resultCad->currency);
        $this->assertEquals('カナダ・ドル', $resultCad->currency_name);
        $this->assertEquals('108.840000', $resultCad->tts);
        $this->assertEquals('105.640000', $resultCad->ttb);
        $this->assertEquals('107.240000', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD取得_TWD全未取得(): void
    {
        // Setup
        $year = 2023;
        $month = 12;
        $insertData = $this->createInsertDataForParseForeignRate($year, $month);
        $resultCollection = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currencyName' => 'マレーシア・リンギット',
                    'currencyCode' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($insertData);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)
            ->willReturn($insertData);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 2件登録されていることをチェック(USDはモックで設定しているためDBにない)
        $this->assertEquals(2, $result->count());

        // TWD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'TWD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('TWD', $resultCad->currency_code);
        $this->assertEquals('New Taiwan Dollar', $resultCad->currency);
        $this->assertEquals('台湾ドル', $resultCad->currency_name);
        $this->assertEquals('0.218900', $resultCad->tts);
        $this->assertEquals('0.214900', $resultCad->ttb);
        $this->assertEquals('0.216900', $resultCad->ttm);

        // MYR チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'MYR');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('MYR', $resultCad->currency_code);
        $this->assertEquals('Malaysian Ringgit', $resultCad->currency);
        $this->assertEquals('マレーシア・リンギット', $resultCad->currency_name);
        $this->assertEquals('3.303000', $resultCad->tts);
        $this->assertEquals('3.183000', $resultCad->ttb);
        $this->assertEquals('3.243000', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD取得_TWD一部未取得(): void
    {
        // Setup
        $year = 2023;
        $month = 12;
        $insertData = $this->createInsertDataForParseForeignRate($year, $month);
        $insertData->push([
            'currency' => 'Malaysian Ringgit',
            'currencyName' => 'マレーシア・リンギット',
            'currencyCode' => 'MYR',
            'tts' => '3.3030',
            'ttb' => '3.1830',
        ]);
        $resultCollection = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($insertData);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)
            ->willReturn($insertData);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 1件登録されていることをチェック(USDはモックで設定しているためDBにない)
        $this->assertEquals(1, $result->count());

        // TWD チェック
        $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'TWD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('TWD', $resultCad->currency_code);
        $this->assertEquals('New Taiwan Dollar', $resultCad->currency);
        $this->assertEquals('台湾ドル', $resultCad->currency_name);
        $this->assertEquals('0.218900', $resultCad->tts);
        $this->assertEquals('0.214900', $resultCad->ttb);
        $this->assertEquals('0.216900', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_正常実行_USD取得_TWD取得(): void
    {
        // Setup
        $year = 2023;
        $month = 12;
        $insertData = $this->createInsertDataForParseForeignRate($year, $month);
        $insertData2 = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currency_name' => '台湾ドル',
                    'currency_code' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currency_name' => 'マレーシア・リンギット',
                    'currency_code' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );
        $insertData = $insertData->merge($insertData2);

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($insertData);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($insertData);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)
            ->willReturn($insertData);

        // Exercise
        Log::shouldReceive('info')
            ->once()
            ->with('外貨為替定期収集コマンド 2023年12月末更新分:取得済みの為終了', Mockery::type('array'))
            ->andReturn(true);

        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

    }

    private function createInsertDataForParseForeignRate(int $year, int $month): Collection
    {
        return collect(
            [
                [
                    'id' => 1,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'USD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 2,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'EUR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 3,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'CAD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 4,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'GBP',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 5,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'CHF',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 6,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'DKK',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 7,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'NOK',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 8,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'SEK',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 9,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'AUD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 10,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'NZD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 11,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'HKD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 12,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'SGD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 13,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'SAR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 14,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'AED',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 15,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'CNY',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 16,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'THB',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 17,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'INR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 18,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'PKR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 19,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'KWD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 20,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'QAR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 21,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'IDR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 22,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'MXN',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 23,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'KRW',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 24,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'PHP',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 25,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'ZAR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 26,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'CZK',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 27,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'RUB',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 28,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'HUF',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 29,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'PLN',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 30,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'TRY',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
            ]
        );
    }

    private function createInsertDataForParseLocalReference(int $year, int $month): Collection
    {
        return collect(
            [
                [
                    'id' => 100,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'TWD',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
                [
                    'id' => 101,
                    'year' => $year,
                    'month' => $month,
                    'currency_code' => 'MYR',
                    'currency' => 'test',
                    'currency_name' => 'コードの名前',
                    'tts' => '100.00',
                    'ttb' => '200.00',
                    'ttm' => '150.00',
                ],
            ]
        );
    }

    #[Test]
    public function scrapeForeignCurrencyRate_取得情報が空だった(): void
    {
        // Setup
        $resultCollection = collect();
        $year = 2023;
        $month = 12;

        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)->willReturn(collect());

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値もここで指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        // parseの結果が空だった場合、想定したログが出力されること
        // 検証したい内容となるが、Exerciseより先に書く必要がある為ここに記載している
        Log::shouldReceive('info')
            ->once()
            ->with('外貨為替情報が空だった', Mockery::type('array'))
            ->andReturn(true);

        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyService->scrapeForeignCurrencyRate($year, $month);

        $this->assertFalse($ret->isForeignRateSuccess());
        $this->assertEquals("{$year}年{$month}月 月末・月中平均の為替相場が取得できませんでした", $ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertFalse($ret->isLocalReferenceSuccess());
        $this->assertEquals("{$year}年{$month}月 現地参考為替相場が取得できませんでした", $ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());
    }

    #[Test]
    #[DataProvider('scrapeForeignCurrencyRateExceptionData')]
    public function scrapeForeignCurrencyRate_例外発生(string $errorMsg): void
    {
        // Setup
        $year = 2023;
        $month = 12;

        $resultCollection2 = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currencyName' => 'マレーシア・リンギット',
                    'currencyCode' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );

        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape', 'getAdmForeignCurrencyRateCollection']);
        $currencyAdminServiceMock->method('getAdmForeignCurrencyRateCollection')->with($year, $month)->willReturn(collect());
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値もここで指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willThrowException(new \Exception($errorMsg));
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection2);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        $ret = $currencyAdminServiceMock->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertFalse($ret->isForeignRateSuccess());
        $this->assertEquals("外貨為替定期収集コマンド {$year}年{$month}月末更新分:月末・月中平均の為替相場取得時にエラーが発生しました", $ret->getForeignRateErrorMessage());
        $this->assertEquals($errorMsg, $ret->getForeignRateException()->getMessage());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());
    }

    #[Test]
    public function scrapeForeignCurrencyRate_例外発生_USDで例外、TWDは未取得()
    {
        // Setup
        $year = 2023;
        $month = 12;
        $errorMsg = "為替データ取得でエラーが発生しました";

        $resultCollection2 = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currencyName' => 'マレーシア・リンギット',
                    'currencyCode' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値もここで指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willThrowException(new \Exception($errorMsg));
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection2);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        $ret = $currencyAdminServiceMock->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertFalse($ret->isForeignRateSuccess());
        $this->assertEquals("外貨為替定期収集コマンド {$year}年{$month}月末更新分:月末・月中平均の為替相場取得時にエラーが発生しました", $ret->getForeignRateErrorMessage());
        $this->assertEquals($errorMsg, $ret->getForeignRateException()->getMessage());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        // 登録データの照合
        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 2件登録されていることをチェック
        $this->assertEquals(2, $result->count());

        // TWD チェック
        $resultCad = $result->first(fn(AdmForeignCurrencyRate $row) => $row->currency_code === 'TWD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('TWD', $resultCad->currency_code);
        $this->assertEquals('New Taiwan Dollar', $resultCad->currency);
        $this->assertEquals('台湾ドル', $resultCad->currency_name);
        $this->assertEquals('0.218900', $resultCad->tts);
        $this->assertEquals('0.214900', $resultCad->ttb);
        $this->assertEquals('0.216900', $resultCad->ttm);

        // MYR チェック
        $resultCad = $result->first(fn(AdmForeignCurrencyRate $row) => $row->currency_code === 'MYR');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('MYR', $resultCad->currency_code);
        $this->assertEquals('Malaysian Ringgit', $resultCad->currency);
        $this->assertEquals('マレーシア・リンギット', $resultCad->currency_name);
        $this->assertEquals('3.303000', $resultCad->tts);
        $this->assertEquals('3.183000', $resultCad->ttb);
        $this->assertEquals('3.243000', $resultCad->ttm);
    }

    #[Test]
    public function scrapeForeignCurrencyRate_例外発生_USDは未取得、TWDで例外()
    {
        // Setup
        $year = 2023;
        $month = 12;
        $errorMsg = "為替データ取得でエラーが発生しました";

        $resultCollection1 = collect(
            [
                [
                    'currency' => 'US Dollar',
                    'currencyName' => '米ドル',
                    'currencyCode' => 'USD',
                    'tts' => '142.83',
                    'ttb' => '140.83',
                ],
                [
                    'currency' => 'Euro',
                    'currencyName' => 'ユーロ',
                    'currencyCode' => 'EUR',
                    'tts' => '158.62',
                    'ttb' => '155.62',
                ],
                [
                    'currency' => 'Canadian Dollar',
                    'currencyName' => 'カナダ・ドル',
                    'currencyCode' => 'CAD',
                    'tts' => '108.84',
                    'ttb' => '105.64',
                ],
            ]
        );

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値もここで指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection1);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willThrowException(new \Exception($errorMsg));

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        $ret = $currencyAdminServiceMock->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertFalse($ret->isLocalReferenceSuccess());
        $this->assertEquals("外貨為替定期収集コマンド {$year}年{$month}月末更新分:現地参考為替相場取得時にエラーが発生しました", $ret->getLocalReferenceErrorMessage());
        $this->assertEquals($errorMsg, $ret->getLocalReferenceException()->getMessage());

        // 登録データの照合
        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 2件登録されていることをチェック
        $this->assertEquals(3, $result->count());

        // USD チェック
        $resultUsd = $result->first(fn(AdmForeignCurrencyRate $row) => $row->currency_code === 'USD');
        $this->assertEquals('2023', $resultUsd->year);
        $this->assertEquals('12', $resultUsd->month);
        $this->assertEquals('USD', $resultUsd->currency_code);
        $this->assertEquals('US Dollar', $resultUsd->currency);
        $this->assertEquals('米ドル', $resultUsd->currency_name);
        $this->assertEquals('142.830000', $resultUsd->tts);
        $this->assertEquals('140.830000', $resultUsd->ttb);
        $this->assertEquals('141.830000', $resultUsd->ttm);

        // EUR チェック
        $resultEur = $result->first(fn(AdmForeignCurrencyRate $row) => $row->currency_code === 'EUR');
        $this->assertEquals('2023', $resultEur->year);
        $this->assertEquals('12', $resultEur->month);
        $this->assertEquals('EUR', $resultEur->currency_code);
        $this->assertEquals('Euro', $resultEur->currency);
        $this->assertEquals('ユーロ', $resultEur->currency_name);
        $this->assertEquals('158.620000', $resultEur->tts);
        $this->assertEquals('155.620000', $resultEur->ttb);
        $this->assertEquals('157.120000', $resultEur->ttm);

        // CAD チェック
        $resultCad = $result->first(fn(AdmForeignCurrencyRate $row) => $row->currency_code === 'CAD');
        $this->assertEquals('2023', $resultCad->year);
        $this->assertEquals('12', $resultCad->month);
        $this->assertEquals('CAD', $resultCad->currency_code);
        $this->assertEquals('Canadian Dollar', $resultCad->currency);
        $this->assertEquals('カナダ・ドル', $resultCad->currency_name);
        $this->assertEquals('108.840000', $resultCad->tts);
        $this->assertEquals('105.640000', $resultCad->ttb);
        $this->assertEquals('107.240000', $resultCad->ttm);
    }

    /**
     * @return array
     */
    public static function scrapeForeignCurrencyRateExceptionData(): array
    {
        return [
            'h2テキストの解析でエラー' => [
                '外貨為替収集情報取得:h2テキストの解析でエラー'
            ],
            '指定年月の情報がなかった' => [
                '外貨為替収集情報取得:指定年月の情報がなかった'
            ],
            'tableデータの解析でエラー' => [
                '外貨為替収集情報取得:tableデータの解析でエラー'
            ],
            'tableデータの解析結果がnull' => [
                '外貨為替収集情報取得:tableデータの解析結果がnull'
            ],
            'ttsが数字に変換できなかった' => [
                '外貨為替収集情報取得:ttsが数字に変換できなかった'
            ],
            'ttbが数字に変換できなかった' => [
                '外貨為替収集情報取得:ttbが数字に変換できなかった'
            ],
        ];
    }

    public static function scrapeForeignCurrencyRateConfigData(): array
    {
        return [
            '両方取得' => [
                // 'wp_currency.enable_scrape_foreign_rate'
                true,
                // 'wp_currency.enable_scrape_local_reference'
                true
            ],
            '外貨為替情報のみ取得' => [
                // 'wp_currency.enable_scrape_foreign_rate'
                true,
                // 'wp_currency.enable_scrape_local_reference'
                false
            ],
            '現地参考為替相場のみ取得' => [
                // 'wp_currency.enable_scrape_foreign_rate'
                false,
                // 'wp_currency.enable_scrape_local_reference'
                true
            ],
            '両方取得しない' => [
                // 'wp_currency.enable_scrape_foreign_rate'
                false,
                // 'wp_currency.enable_scrape_local_reference'
                false
            ],
        ];
    }

    #[Test]
    #[DataProvider('scrapeForeignCurrencyRateConfigData')]
    public function scrapeForeignCurrencyRate_正常実行_取得実行の設定(
        $enableScrapForeignRate,
        $enableScrapLocalReference
    ): void {
        // Setup
        config([
            'wp_currency.enable_scrape_foreign_rate' => $enableScrapForeignRate,
            'wp_currency.enable_scrape_local_reference' => $enableScrapLocalReference
        ]);

        $resultCollection = collect(
            [
                [
                    'currency' => 'US Dollar',
                    'currencyName' => '米ドル',
                    'currencyCode' => 'USD',
                    'tts' => '142.83',
                    'ttb' => '140.83',
                ],
                [
                    'currency' => 'Euro',
                    'currencyName' => 'ユーロ',
                    'currencyCode' => 'EUR',
                    'tts' => '158.62',
                    'ttb' => '155.62',
                ],
                [
                    'currency' => 'Canadian Dollar',
                    'currencyName' => 'カナダ・ドル',
                    'currencyCode' => 'CAD',
                    'tts' => '108.84',
                    'ttb' => '105.64',
                ],
            ]
        );
        $resultCollection2 = collect(
            [
                [
                    'currency' => 'New Taiwan Dollar',
                    'currencyName' => '台湾ドル',
                    'currencyCode' => 'TWD',
                    'tts' => '0.2189',
                    'ttb' => '0.2149',
                ],
                [
                    'currency' => 'Malaysian Ringgit',
                    'currencyName' => 'マレーシア・リンギット',
                    'currencyCode' => 'MYR',
                    'tts' => '3.3030',
                    'ttb' => '3.1830',
                ],
            ]
        );
        $year = 2023;
        $month = 12;

        // CurrencyAdminServiceのモックではコンストラクタが呼び出されず、AdmForeignCurrencyRateRepositoryが初期化されず呼び出されてエラーになってしまう
        // その為コンストラクの内容を注入して生成している
        $currencyAdminServiceMock = $this->createPartialMock(CurrencyAdminService::class, ['createForeignCurrencyRateScrape']);
        $reflectedClass = new ReflectionClass(CurrencyAdminService::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke(
            $currencyAdminServiceMock,
            $this->usrCurrencyPaidRepository,
            $this->usrCurrencyFreeRepository,
            $this->logCurrencyPaidRepository,
            $this->logCurrencyFreeRepository,
            $this->logCurrencyRevertHistoryRepository,
            $this->logCurrencyRevertHistoryPaidLogRepository,
            $this->logCurrencyRevertHistoryFreeLogRepository,
            $this->currencyService,
            $this->admForeignCurrencyRateRepository,
            $this->admForeignCurrencyDailyRateRepository,
            $this->oprProductRepository,
            $this->mstStoreProductRepository,
            $this->unionLogCurrencyRepository,
        );

        // ForeignCurrencyRateScrapeのモックを作成
        // parseメソッドの返り値を指定する
        $foreignCurrencyRateScrapeMock = $this->createMock(ForeignCurrencyRateScrape::class);
        $foreignCurrencyRateScrapeMock
            ->method('parse')
            ->with($year, $month)
            ->willReturn($resultCollection);
        $foreignCurrencyRateScrapeMock
            ->method('parseLocalReferenceExchangeRateByExcel')
            ->with($year, $month)
            ->willReturn($resultCollection2);

        // createForeignCurrencyRateScrapeメソッドがモックのForeignCurrencyRateScrapeオブジェクトを返すように設定
        $currencyAdminServiceMock->method('createForeignCurrencyRateScrape')
            ->willReturn($foreignCurrencyRateScrapeMock);

        // Exercise
        app()->instance(CurrencyAdminService::class, $currencyAdminServiceMock);
        $currencyAdminService = $this->app->make(CurrencyAdminService::class);
        $ret = $currencyAdminService->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $this->assertTrue($ret->isForeignRateSuccess());
        $this->assertEmpty($ret->getForeignRateErrorMessage());
        $this->assertNull($ret->getForeignRateException());
        $this->assertTrue($ret->isLocalReferenceSuccess());
        $this->assertEmpty($ret->getLocalReferenceErrorMessage());
        $this->assertNull($ret->getLocalReferenceException());

        // 登録データの照合
        if ($enableScrapForeignRate && $enableScrapLocalReference) {
            $existsCount = 5;
        } elseif ($enableScrapForeignRate && !$enableScrapLocalReference) {
            $existsCount = 3;
        } elseif (!$enableScrapForeignRate && $enableScrapLocalReference) {
            $existsCount = 2;
        } else {
            $existsCount = 0;
        }

        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 5件登録されていることをチェック
        $this->assertEquals($existsCount, $result->count());

        if ( $enableScrapForeignRate ) {
            // USD チェック
            $resultUsd = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'USD');
            $this->assertEquals('2023', $resultUsd->year);
            $this->assertEquals('12', $resultUsd->month);
            $this->assertEquals('USD', $resultUsd->currency_code);
            $this->assertEquals('US Dollar', $resultUsd->currency);
            $this->assertEquals('米ドル', $resultUsd->currency_name);
            $this->assertEquals('142.830000', $resultUsd->tts);
            $this->assertEquals('140.830000', $resultUsd->ttb);
            $this->assertEquals('141.830000', $resultUsd->ttm);

            // EUR チェック
            $resultEur = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'EUR');
            $this->assertEquals('2023', $resultEur->year);
            $this->assertEquals('12', $resultEur->month);
            $this->assertEquals('EUR', $resultEur->currency_code);
            $this->assertEquals('Euro', $resultEur->currency);
            $this->assertEquals('ユーロ', $resultEur->currency_name);
            $this->assertEquals('158.620000', $resultEur->tts);
            $this->assertEquals('155.620000', $resultEur->ttb);
            $this->assertEquals('157.120000', $resultEur->ttm);

            // CAD チェック
            $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'CAD');
            $this->assertEquals('2023', $resultCad->year);
            $this->assertEquals('12', $resultCad->month);
            $this->assertEquals('CAD', $resultCad->currency_code);
            $this->assertEquals('Canadian Dollar', $resultCad->currency);
            $this->assertEquals('カナダ・ドル', $resultCad->currency_name);
            $this->assertEquals('108.840000', $resultCad->tts);
            $this->assertEquals('105.640000', $resultCad->ttb);
            $this->assertEquals('107.240000', $resultCad->ttm);
        }

        if ($enableScrapLocalReference) {
            // TWD チェック
            $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'TWD');
            $this->assertEquals('2023', $resultCad->year);
            $this->assertEquals('12', $resultCad->month);
            $this->assertEquals('TWD', $resultCad->currency_code);
            $this->assertEquals('New Taiwan Dollar', $resultCad->currency);
            $this->assertEquals('台湾ドル', $resultCad->currency_name);
            $this->assertEquals('0.218900', $resultCad->tts);
            $this->assertEquals('0.214900', $resultCad->ttb);
            $this->assertEquals('0.216900', $resultCad->ttm);

            // MYR チェック
            $resultCad = $result->first(fn (AdmForeignCurrencyRate $row) => $row->currency_code === 'MYR');
            $this->assertEquals('2023', $resultCad->year);
            $this->assertEquals('12', $resultCad->month);
            $this->assertEquals('MYR', $resultCad->currency_code);
            $this->assertEquals('Malaysian Ringgit', $resultCad->currency);
            $this->assertEquals('マレーシア・リンギット', $resultCad->currency_name);
            $this->assertEquals('3.303000', $resultCad->tts);
            $this->assertEquals('3.183000', $resultCad->ttb);
            $this->assertEquals('3.243000', $resultCad->ttm);
        }
    }

    #[Test]
    public function collectFreeCurrencyByCollectPaid_回収実行(): void
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            500,
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // 有償一次通貨回収用のtriggerを生成
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_test');

        // Exercise
        $this->currencyAdminService->collectFreeCurrencyByCollectPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            100,
            $trigger
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(400, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1');
        $log = collect($logs)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals('1', $log->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $log->os_platform);
        $this->assertEquals(100, $log->before_ingame_amount);
        $this->assertEquals(500, $log->before_bonus_amount);
        $this->assertEquals(0, $log->before_reward_amount);
        $this->assertEquals(0, $log->change_ingame_amount);
        $this->assertEquals(-100, $log->change_bonus_amount);
        $this->assertEquals(0, $log->change_reward_amount);
        $this->assertEquals(100, $log->current_ingame_amount);
        $this->assertEquals(400, $log->current_bonus_amount);
        $this->assertEquals(0, $log->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN, $log->trigger_type);
        $this->assertEquals('usr_store_product_history_id', $log->trigger_id);
        $this->assertEquals('', $log->trigger_name);
        $this->assertEquals('trigger_detail_test', $log->trigger_detail);
    }

    #[Test]
    public function collectFreeCurrencyByCollectPaid_回収した結果マイナス値になる(): void
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            500,
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // 有償一次通貨回収用のtriggerを生成
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_test');

        // Exercise
        $this->currencyAdminService->collectFreeCurrencyByCollectPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            600,
            $trigger
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(0, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(-100, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1');
        $log = collect($logs)->first(fn ($row) => $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN);
        $this->assertEquals('1', $log->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $log->os_platform);
        $this->assertEquals(100, $log->before_ingame_amount);
        $this->assertEquals(500, $log->before_bonus_amount);
        $this->assertEquals(0, $log->before_reward_amount);
        $this->assertEquals(0, $log->change_ingame_amount);
        $this->assertEquals(-600, $log->change_bonus_amount);
        $this->assertEquals(0, $log->change_reward_amount);
        $this->assertEquals(100, $log->current_ingame_amount);
        $this->assertEquals(-100, $log->current_bonus_amount);
        $this->assertEquals(0, $log->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN, $log->trigger_type);
        $this->assertEquals('usr_store_product_history_id', $log->trigger_id);
        $this->assertEquals('', $log->trigger_name);
        $this->assertEquals('trigger_detail_test', $log->trigger_detail);
    }

    #[Test]
    public function getOprProductById_取得(): void
    {
        // Setup
        $this->insertOptProduct('1', 0, '1-1', 10);

        // Exercise
        $result = $this->currencyAdminService
            ->getOprProductById('1');

        // Verify
        $this->assertEquals('1', $result->id);
        $this->assertEquals('1-1', $result->mst_store_product_id);
        $this->assertEquals(10, $result->paid_amount);
    }

    #[Test]
    public function getOprProductById_取得結果がnull(): void
    {
        // Exercise
        $result = $this->currencyAdminService
            ->getOprProductById('1');

        // Verify
        $this->assertNull($result);
    }

    #[Test]
    public function getMstStoreProductById_取得(): void
    {
        // Setup
        $this->insertMstStoreProduct('1-1', 0, 'ap-1', 'gg-1');

        // Exercise
        $result = $this->currencyAdminService
            ->getMstStoreProductById('1-1');

        // Verify
        $this->assertEquals('1-1', $result->id);
        $this->assertEquals(0, $result->release_key);
        $this->assertEquals('ap-1', $result->product_id_ios);
        $this->assertEquals('gg-1', $result->product_id_android);
    }

    #[Test]
    public function getMstStoreProductById_取得結果がnull(): void
    {
        // Exercise
        $result = $this->currencyAdminService
            ->getMstStoreProductById('1-1');

        // Verify
        $this->assertNull($result);
    }

    #[Test]
    public function collectCurrencyPaid_回収処理実行(): void
    {
        // Setup
        $userId = '100';
        $amount = 1000;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $currencyCode = 'JPY';
        $price = '1000.00000000';
        $vipPoint = 0;
        $isSandbox = false;
        // ユーザー作成
        $this->currencyService->createUser(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨購入情報を作成
        //  apple1回目
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                50,
                'JPY',
                '50.00',
                10,
                'purchased_receipt_unique_id_1',
                false,
                new Trigger(
                    'purchased1',
                    'purchased_trigger_id1',
                    'purchased_trigger_name1',
                    'purchased_trigger_detail1'
                )
            );
        //  google1回目
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_ANDROID,
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                100,
                'JPY',
                '100.00',
                0,
                'purchased_receipt_unique_id_2',
                $isSandbox,
                new Trigger(
                    'purchased2',
                    'purchased_trigger_id2',
                    'purchased_trigger_name2',
                    'purchased_trigger_detail2'
                )
            );
        //  apple2回目(回収対象)
        $collectTargetReceiptUniqueId = 'purchased_receipt_unique_id_3';
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $amount,
                $currencyCode,
                $price,
                $vipPoint,
                $collectTargetReceiptUniqueId,
                $isSandbox,
                new Trigger(
                    'purchased3',
                    'purchased_trigger_id3',
                    'purchased_trigger_name3',
                    'purchased_trigger_detail3'
                )
            );

        // 回収処理実行用パラメータ
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_test');

        // Exercise
        $usrCurrencyPaid = $this->currencyAdminService
            ->collectCurrencyPaid(
                $userId,
                $billingPlatform,
                $collectTargetReceiptUniqueId,
                $receiptUniqueId,
                $isSandbox,
                $trigger
            );

        // Verify
        //  usrCurrencyPaid
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);
        //   レコードが3件ある
        $this->assertCount(3, $usrCurrencyPaids);
        //   回収レコードの減算チェック
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals(0, $usrCurrencyPaid->left_amount);
        $this->assertEquals($collectTargetReceiptUniqueId, $usrCurrencyPaid->receipt_unique_id);

        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(50, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(100, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(150, $usrCurrencySummary->getTotalPaidAmount());

        //  logCurrencyPaidのチェック
        //   レコードが4件ある
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        $this->assertCount(4, $logCurrencyPaids);
        //   回収ログのチェック
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) use ($usrCurrencyPaid) {
                return $row->currency_paid_id === $usrCurrencyPaid->id
                    && $row->query === LogCurrencyPaid::QUERY_UPDATE;
            }
        );
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals(3, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('1000.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(-1 * $vipPoint, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(1050, $logCurrencyPaid->before_amount);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->change_amount);
        $this->assertEquals(50, $logCurrencyPaid->current_amount);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals('collect_currency_paid', $logCurrencyPaid->trigger_type);
        $this->assertEquals('usr_store_product_history_id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_test', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function collectCurrencyPaid_回収した結果マイナスになる(): void
    {
        // Setup
        $userId = '100';
        $amount = 1000;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $currencyCode = 'JPY';
        $price = '1000.00000000';
        $vipPoint = 0;
        $isSandbox = false;
        // ユーザー作成
        $this->currencyService->createUser(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨購入情報を作成
        //  google
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_ANDROID,
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                100,
                'JPY',
                '100.00',
                0,
                'purchased_receipt_unique_id_1',
                $isSandbox,
                new Trigger(
                    'purchased1',
                    'purchased_trigger_id1',
                    'purchased_trigger_name1',
                    'purchased_trigger_detail1'
                )
            );
        //  apple(回収対象)
        $collectTargetReceiptUniqueId = 'purchased_receipt_unique_id_2';
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $amount,
                $currencyCode,
                $price,
                $vipPoint,
                $collectTargetReceiptUniqueId,
                $isSandbox,
                new Trigger(
                    'purchased2',
                    'purchased_trigger_id2',
                    'purchased_trigger_name2',
                    'purchased_trigger_detail2'
                )
            );
        // apple通貨100消費
        $this->currencyService->usePaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $billingPlatform,
            100,
            new Trigger(
                'consume',
                'test_trigger_id',
                'test_trigger_name',
                'test_trigger_detail'
            ),
        );
        // 回収処理実行用パラメータ
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_test');

        // Exercise
        $usrCurrencyPaid = $this->currencyAdminService
            ->collectCurrencyPaid(
                $userId,
                $billingPlatform,
                $collectTargetReceiptUniqueId,
                $receiptUniqueId,
                $isSandbox,
                $trigger
            );

        // Verify
        //  usrCurrencyPaid
        $usrCurrencyPaids = $this->usrCurrencyPaidRepository->findByUserId($userId);
        //   レコードが2件ある
        $this->assertCount(2, $usrCurrencyPaids);
        //   回収レコードの減算チェック(left_amountがマイナスになっていること)
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals(-100, $usrCurrencyPaid->left_amount);
        $this->assertEquals($collectTargetReceiptUniqueId, $usrCurrencyPaid->receipt_unique_id);

        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック(paid_amount_appleがマイナスになっていること)
        $this->assertEquals(-100, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(100, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(0, $usrCurrencySummary->getTotalPaidAmount());

        //  logCurrencyPaidのチェック
        //   レコードが4件ある(購入2、消費1、回収1)
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        $this->assertCount(4, $logCurrencyPaids);
        //   回収ログのチェック
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) use ($usrCurrencyPaid) {
                return $row->currency_paid_id === $usrCurrencyPaid->id
                    && $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN;
            }
        );
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals(2, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('1000.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(0, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(900, $logCurrencyPaid->before_amount);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->change_amount);
        $this->assertEquals(-100, $logCurrencyPaid->current_amount);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals('collect_currency_paid', $logCurrencyPaid->trigger_type);
        $this->assertEquals('usr_store_product_history_id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_test', $logCurrencyPaid->trigger_detail);

        //  usrCurrencyFreeのチェック
        $usrCurrencyFree = $this->currencyService->getCurrencyFree($userId);
        $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);
    }

    #[Test]
    public function collectCurrencyPaid_回収対象のusrCurrencyPaidが取得できない(): void
    {
        // Setup
        $userId = '100';
        $amount = 50;
        $isSandbox = false;
        // ユーザー作成
        $this->currencyService->createUser(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨購入情報を作成
        //  apple1回目
        $this->currencyService
            ->addCurrencyPaid(
                $userId,
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                $amount,
                'JPY',
                '50.00',
                10,
                'purchased_receipt_unique_id_1',
                false,
                new Trigger(
                    'purchased1',
                    'purchased_trigger_id1',
                    'purchased_trigger_name1',
                    'purchased_trigger_detail1'
                )
            );
        // 回収処理実行用パラメータ
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id', 'trigger_detail_test');

        // Exercise
        $this->expectException(WpCurrencyException::class);
        $this->expectExceptionMessage('Currency-14: usr_currency_paid not found userId=100, receiptUniqueId=purchased_receipt_unique_id_unknown, billingPlatform=AppStore');
        $this->currencyAdminService
            ->collectCurrencyPaid(
                $userId,
                CurrencyConstants::PLATFORM_APPSTORE,
                'purchased_receipt_unique_id_unknown',
                $receiptUniqueId,
                $isSandbox,
                $trigger
            );
    }

    /**
     * ユニットテストで使用するテストデータを作成する
     *
     * @return void
     */
    private function setupTestData(): void
    {
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2022-12-30 10:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            seqNo: 2,
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 100,
            changeAmount: -50,
            currentAmount: 50,
            triggerType: 'consume',
            createdAtJstStr: '2023-01-01 10:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '200',
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            currencyPaidId: '2',
            receiptUniqueId: 'receipt_unique_id_2',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '200',
            osPlatform: CurrencyConstants::OS_PLATFORM_ANDROID,
            billingPlatform: CurrencyConstants::PLATFORM_GOOGLEPLAY,
            seqNo: 2,
            currencyPaidId: '2',
            receiptUniqueId: 'receipt_unique_id_2',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-31 23:59:59'
        );
        // 論理削除ユーザーデータ
        $this->makeLogCurrencyPaidRecord(
            userId: '999',
            currencyPaidId: '3',
            receiptUniqueId: 'receipt_unique_id_3',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-11 12:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '999',
            seqNo: 2,
            currencyPaidId: '4',
            receiptUniqueId: 'receipt_unique_id_4',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 100,
            changeAmount: 100,
            currentAmount: 200,
            createdAtJstStr: '2023-12-11 12:10:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '999',
            seqNo: 3,
            currencyPaidId: '3',
            receiptUniqueId: 'receipt_unique_id_3',
            query: LogCurrencyPaid::QUERY_UPDATE,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 200,
            changeAmount: -50,
            currentAmount: 150,
            triggerType: 'consume',
            createdAtJstStr: '2023-12-11 12:20:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '999',
            seqNo: 3,
            currencyPaidId: '3',
            receiptUniqueId: 'receipt_unique_id_3',
            query: LogCurrencyPaid::QUERY_DELETE,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 150,
            changeAmount: -50,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-11 13:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '999',
            seqNo: 4,
            currencyPaidId: '4',
            receiptUniqueId: 'receipt_unique_id_4',
            query: LogCurrencyPaid::QUERY_DELETE,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            beforeAmount: 100,
            changeAmount: -100,
            triggerType: Trigger::TRIGGER_TYPE_DELETE_USER,
            createdAtJstStr: '2023-12-11 13:00:00'
        );
        // 通貨コードがJPYじゃない
        $this->makeLogCurrencyPaidRecord(
            userId: '300',
            currencyPaidId: '10',
            receiptUniqueId: 'receipt_unique_id_10',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-21 13:00:00'
        );
        // sandbox環境を使用
        $this->makeLogCurrencyPaidRecord(
            userId: '400',
            currencyPaidId: '11',
            receiptUniqueId: 'receipt_unique_id_11',
            isSandbox: true,
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-21 13:00:01'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '400',
            currencyPaidId: '11',
            receiptUniqueId: 'receipt_unique_id_11',
            isSandbox: true,
            purchasePrice: '2000',
            purchaseAmount: 1,
            pricePerAmount: '2000',
            currencyCode: 'USD',
            changeAmount: 1,
            currentAmount: 1,
            createdAtJstStr: '2023-12-21 13:00:01'
        );
        // 回収ツールによる回収
        //  回収対象データ(2件)
        $this->makeLogCurrencyPaidRecord(
            userId: '500',
            currencyPaidId: '500',
            receiptUniqueId: 'receipt_unique_id_500_1',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-22 00:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '500',
            currencyPaidId: '501',
            receiptUniqueId: 'receipt_unique_id_500_2',
            purchasePrice: '999',
            purchaseAmount: 1,
            pricePerAmount: '999',
            beforeAmount: 100,
            changeAmount: 1,
            currentAmount: 101,
            createdAtJstStr: '2023-12-22 00:30:00'
        );
        //  回収データ(2件)
        $this->makeLogCurrencyPaidRecord(
            userId: '500',
            currencyPaidId: '500',
            receiptUniqueId: 'receipt_unique_id_500_1',
            purchasePrice: '-1000',
            purchaseAmount: -100,
            pricePerAmount: '10',
            beforeAmount: 201,
            changeAmount: -100,
            currentAmount: 101,
            triggerType: Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_BATCH,
            triggerId: 'usr_product_history_id_500_1',
            createdAtJstStr: '2023-12-23 12:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '500',
            currencyPaidId: '501',
            receiptUniqueId: 'receipt_unique_id_500_2',
            purchasePrice: '-999',
            purchaseAmount: -1,
            pricePerAmount: '999',
            beforeAmount: 1,
            changeAmount: -1,
            triggerType: Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN,
            triggerId: 'usr_product_history_id_1000_2',
            createdAtJstStr: '2023-12-23 12:10:00'
        );
        // 作成日が対象外
        $this->makeLogCurrencyPaidRecord(
            seqNo: 3,
            currencyPaidId: '11',
            receiptUniqueId: 'receipt_unique_id_11',
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2024-01-01 00:00:00'
        );
    }

    /**
     * log_currency_paids レコード作成
     */
    private function makeLogCurrencyPaidRecord(
        $userId = '100',
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

    public static function getYearOptionsData(): array
    {
        return [
            '2023年〜2025年' => [
                Carbon::create(2025),
                [
                    '2023' => '2023',
                    '2024' => '2024',
                    '2025' => '2025',
                ]
            ],
            '2023年のみ' => [
                Carbon::create(2023),
                [
                    '2023' => '2023',
                ]
            ],
        ];
    }

    /**
     * 外貨為替レートデータ作成
     */
    private function makeAdmForeignCurrencyRate(): void
    {
        $inputs = [
            [
                'id' => '1',
                'year' => '2023',
                'month' => '11',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '100.00',
                'ttb' => '200.00',
                'ttm' => '150.00',
            ],
            [
                'id' => '2',
                'year' => '2023',
                'month' => '12',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '150.58',
                'ttb' => '148.58',
                'ttm' => '149.58',
            ],
        ];
        AdmForeignCurrencyRate::query()->insert($inputs);
    }

    #[Test]
    public function collectFreeCurrency_無償一次通貨を回収する(): void
    {
        // Setup
        // 通貨管理の登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 通貨を追加
        $this->currencyService->addFree(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            500,
            'bonus',
            new Trigger('unit_test', 'sample id', 'sample name', 'detail')
        );
        // ログの削除
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->currencyAdminService->collectFreeCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            'bonus',
            100,
            'collect free currency'
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(500, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(400, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(0, $usrCurrencyFree->reward_amount);

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId('1')[0];
        $this->assertEquals('1', $logs->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logs->os_platform);
        $this->assertEquals(100, $logs->before_ingame_amount);
        $this->assertEquals(500, $logs->before_bonus_amount);
        $this->assertEquals(0, $logs->before_reward_amount);
        $this->assertEquals(0, $logs->change_ingame_amount);
        $this->assertEquals(-100, $logs->change_bonus_amount);
        $this->assertEquals(0, $logs->change_reward_amount);
        $this->assertEquals(100, $logs->current_ingame_amount);
        $this->assertEquals(400, $logs->current_bonus_amount);
        $this->assertEquals(0, $logs->current_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_FREE_ADMIN, $logs->trigger_type);
        $this->assertEquals('', $logs->trigger_id);
        $this->assertEquals('', $logs->trigger_name);
        $this->assertEquals('collect free currency', $logs->trigger_detail);
    }

    #[Test]
    #[DataProvider('addCurrencyFreeData')]
    public function addCurrencyFree_正常処理チェック(string $type): void
    {
        // Setup
        $userId = '100';
        $amount = 999;
        $osPlatform = CurrencyConstants::OS_PLATFORM_BATCH;
        $trigger = new AddFreeCurrencyBatchTrigger(
            'add free currency test'
        );
        //  通貨情報を登録
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $this->currencyAdminService->addCurrencyFree(
            $userId,
            $osPlatform,
            $amount,
            $type,
            $trigger
        );

        // Verify
        // summaryの確認
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        $this->assertEquals($userId, $usrCurrencySummary->usr_user_id);
        $this->assertEquals($amount, $usrCurrencySummary->free_amount);

        // freeの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId($userId);
        $this->assertEquals($userId, $usrCurrencyFree->usr_user_id);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals($amount, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
                $this->assertEquals($amount, $usrCurrencyFree->bonus_amount);
                $this->assertEquals(0, $usrCurrencyFree->reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
                $this->assertEquals(0, $usrCurrencyFree->bonus_amount);
                $this->assertEquals($amount, $usrCurrencyFree->reward_amount);
                break;
        }

        // ログの確認
        $logs = $this->logCurrencyFreeRepository->findByUserId($userId);
        $log = collect($logs)->first(
            fn ($row) => $row['trigger_type'] === Trigger::TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH
        );
        $this->assertEquals($userId, $log->usr_user_id);
        $this->assertEquals($osPlatform, $log->os_platform);
        $this->assertEquals(0, $log->before_ingame_amount);
        $this->assertEquals(0, $log->before_bonus_amount);
        $this->assertEquals(0, $log->before_reward_amount);
        $this->assertEquals(Trigger::TRIGGER_TYPE_ADD_CURRENCY_FREE_BATCH, $log->trigger_type);
        $this->assertEquals('', $log->trigger_id);
        $this->assertEquals('', $log->trigger_name);
        $this->assertEquals('add free currency test', $log->trigger_detail);
        switch ($type) {
            case CurrencyConstants::FREE_CURRENCY_TYPE_INGAME:
                $this->assertEquals($amount, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals($amount, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_BONUS:
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals($amount, $log->change_bonus_amount);
                $this->assertEquals(0, $log->change_reward_amount);
                $this->assertEquals(0, $log->current_ingame_amount);
                $this->assertEquals($amount, $log->current_bonus_amount);
                $this->assertEquals(0, $log->current_reward_amount);
                break;
            case CurrencyConstants::FREE_CURRENCY_TYPE_REWARD:
                $this->assertEquals(0, $log->change_ingame_amount);
                $this->assertEquals(0, $log->change_bonus_amount);
                $this->assertEquals($amount, $log->change_reward_amount);
                $this->assertEquals(0, $log->current_ingame_amount);
                $this->assertEquals(0, $log->current_bonus_amount);
                $this->assertEquals($amount, $log->current_reward_amount);
                break;
        }
    }

    /**
     * @return array[]
     */
    public static function addCurrencyFreeData(): array
    {
        return [
            '対象がbonus' => [CurrencyConstants::FREE_CURRENCY_TYPE_BONUS],
            '対象がingame' => [CurrencyConstants::FREE_CURRENCY_TYPE_INGAME],
            '対象がreward' => [CurrencyConstants::FREE_CURRENCY_TYPE_REWARD],
        ];
    }

    #[Test]
    public function makeCsvBulkLogCurrencyRevertSearch_データ取得()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $startAt = new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo');
        $endAt = new CarbonImmutable('2023-01-31 23:59:59', 'Asia/Tokyo');
        $this->createTestLogCurrencyData($triggerType, $triggerId, 'ガチャ1');

        // Exercise
        $result = $this->currencyAdminService->makeCsvBulkLogCurrencyRevertSearch(
            $startAt,
            $endAt,
            $triggerType,
            $triggerId,
            false,
        );

        // Verify
        $excelDataArray = $result->collection()->toArray();
        $this->assertCount(4, $excelDataArray);
        $headerRow = $excelDataArray[0];
        $this->assertEquals('ユーザーID', $headerRow[0]);
        $this->assertEquals('コンテンツ消費日時', $headerRow[1]);
        $this->assertEquals('消費コンテンツタイプ', $headerRow[2]);
        $this->assertEquals('消費コンテンツID', $headerRow[3]);
        $this->assertEquals('消費コンテンツ名', $headerRow[4]);
        $this->assertEquals('リクエストID', $headerRow[5]);
        $this->assertEquals('消費有償一次通貨数(合計)', $headerRow[6]);
        $this->assertEquals('消費無償一次通貨数(合計)', $headerRow[7]);
        $this->assertEquals('有償一次通貨の消費ログID', $headerRow[8]);
        $this->assertEquals('無償一次通貨の消費ログID', $headerRow[9]);

        $row1 = $excelDataArray[1];
        $this->assertEquals('3', $row1[0]);
        $this->assertEquals('2023-01-31 23:59:59', $row1[1]);
        $this->assertEquals($triggerType, $row1[2]);
        $this->assertEquals($triggerId, $row1[3]);
        $this->assertEquals('ガチャ1', $row1[4]);
        $this->assertEquals('0', $row1[6]);
        $this->assertEquals('-80', $row1[7]);
        $this->assertEmpty($row1[8]);
        $this->assertNotEmpty($row1[9]);

        $row2 = $excelDataArray[2];
        $this->assertEquals('2', $row2[0]);
        $this->assertEquals('2023-01-02 00:00:00', $row2[1]);
        $this->assertEquals($triggerType, $row2[2]);
        $this->assertEquals($triggerId, $row2[3]);
        $this->assertEquals('ガチャ1', $row2[4]);
        $this->assertEquals('-10', $row2[6]);
        $this->assertEquals('-100', $row2[7]);
        $this->assertNotEmpty($row2[8]);
        $this->assertNotEmpty($row2[9]);

        $row3 = $excelDataArray[3];
        $this->assertEquals('1', $row3[0]);
        $this->assertEquals('2023-01-01 00:00:00', $row3[1]);
        $this->assertEquals($triggerType, $row3[2]);
        $this->assertEquals($triggerId, $row3[3]);
        $this->assertEquals('ガチャ1', $row3[4]);
        $this->assertEquals('-90', $row3[6]);
        $this->assertEquals('0', $row3[7]);
        $this->assertNotEmpty($row3[8]);
        $this->assertEmpty($row3[9]);

        // ファイル名が指定した期間から付けられている
        $this->assertEquals(
            '一次通貨返却対象データレポート_20230101000000-20230131235959.csv',
            $result->getFileName(),
        );

    }

    #[Test]
    public function getConsumeLogCurrencyPaidAndFrees_データ取得()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        $startAt = new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo');
        $endAt = new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo');
        $this->createTestLogCurrencyData($triggerType, $triggerId, 'ガチャ1');

        // Exercise
        $result = $this->currencyAdminService->getConsumeLogCurrencyPaidAndFrees(
            $startAt->utc()->toDateTimeString(),
            $endAt->utc()->toDateTimeString(),
            $triggerType,
            $triggerId,
            null,
            false,
        );

        // Verify
        $actual = $result->get();
        $this->assertCount(4, $actual);
        $row1 = $actual->where('usr_user_id', '1')->first();
        $this->assertEquals($triggerId, $row1['trigger_id']);
        $this->assertEquals($triggerType, $row1['trigger_type']);
        $this->assertEquals('2022-12-31 15:00:00', $row1['created_at']->toDateTimeString());
        $this->assertEquals('paid', $row1['log_currency_type']);
        $this->assertEquals('-90', $row1['log_change_amount']);
        $this->assertEquals('-90', $row1['log_change_amount_paid']);
        $this->assertEquals('0', $row1['log_change_amount_free']);
        $row2 = $actual->where('usr_user_id', '2')->where('receipt_unique_id', 'id2')->first();
        $this->assertEquals($triggerId, $row2['trigger_id']);
        $this->assertEquals($triggerType, $row2['trigger_type']);
        $this->assertEquals('2023-01-01 15:00:00', $row2['created_at']->toDateTimeString());
        $this->assertEquals('paid', $row2['log_currency_type']);
        $this->assertEquals('-10', $row2['log_change_amount']);
        $this->assertEquals('-10', $row2['log_change_amount_paid']);
        $this->assertEquals('0', $row2['log_change_amount_free']);
        $row3 = $actual->where('usr_user_id', '2')->where('receipt_unique_id', '')->first();
        $this->assertEquals($triggerId, $row3['trigger_id']);
        $this->assertEquals($triggerType, $row3['trigger_type']);
        $this->assertEquals('2023-01-01 15:00:00', $row3['created_at']->toDateTimeString());
        $this->assertEquals('free', $row3['log_currency_type']);
        $this->assertEquals('-100', $row3['log_change_amount']);
        $this->assertEquals('0', $row3['log_change_amount_paid']);
        $this->assertEquals('-100', $row3['log_change_amount_free']);
        $row4 = $actual->where('usr_user_id', '3')->first();
        $this->assertEquals($triggerId, $row4['trigger_id']);
        $this->assertEquals($triggerType, $row4['trigger_type']);
        $this->assertEquals('2023-01-31 14:59:59', $row4['created_at']->toDateTimeString());
        $this->assertEquals('free', $row4['log_currency_type']);
        $this->assertEquals('-80', $row4['log_change_amount']);
        $this->assertEquals('0', $row4['log_change_amount_paid']);
        $this->assertEquals('-80', $row4['log_change_amount_free']);
    }

    /**
     * 有償のみ・有償無償・無償のみの消費ログを作成する
     * @param string $triggerType
     * @param string $triggerId
     * @param string $triggerName
     * @return void
     */
    private function createTestLogCurrencyData(string $triggerType, string $triggerId, string $triggerName): void
    {
        $this->setTestNow(new Carbon('2023-01-01 00:00:00', 'Asia/Tokyo'));
        // 有償通貨ログのみ
        $this->currencyService->createUser(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'id1',
            false,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 消費
        $this->currencyService->useCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            90,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );

        // 有償、無償ログ
        $this->setTestNow(new Carbon('2023-01-02 00:00:00', 'Asia/Tokyo'));
        $this->currencyService->createUser(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'id2',
            false,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 無償一次通貨の登録　(ログも一緒)
        $this->currencyService->addFree(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            100,
            'ingame',
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 消費
        $this->currencyService->useCurrency(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            110,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );

        // 無償ログ
        $this->setTestNow(new Carbon('2023-01-31 23:59:59', 'Asia/Tokyo'));
        $this->currencyService->createUser(
            '3',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 無償一次通貨の登録　(ログも一緒)
        $this->currencyService->addFree(
            '3',
            CurrencyConstants::OS_PLATFORM_IOS,
            100,
            'ingame',
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 消費
        $this->currencyService->useCurrency(
            '3',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            80,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );

        // 期間外のログ
        $this->setTestNow(new Carbon('2023-02-01 00:00:00', 'Asia/Tokyo'));
        $this->currencyService->createUser(
            '4',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '4',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'id3',
            false,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 消費
        $this->currencyService->useCurrency(
            '4',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            90,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );
        $this->setTestNow(new Carbon('2022-12-31 23:59:59', 'Asia/Tokyo'));
        $this->currencyService->createUser(
            '5',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            '5',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            101,
            'id4',
            false,
            new Trigger('purchased', '1', '', 'add currency'),
        );
        // 消費
        $this->currencyService->useCurrency(
            '5',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            90,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );
    }
}
