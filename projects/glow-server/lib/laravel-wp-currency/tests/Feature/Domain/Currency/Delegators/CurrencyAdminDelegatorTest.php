<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Currency\Delegators;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
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
use WonderPlanet\Domain\Currency\Services\BulkCurrencyRevertTaskService;
use WonderPlanet\Domain\Currency\Services\CurrencyAdminService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregation;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceAggregationByForeignCountry;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyPaidDetail;
use WonderPlanet\Domain\Currency\Utils\Scrape\ForeignCurrencyRateScrape;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class CurrencyAdminDelegatorTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;

    protected $backupConfigKeys = [
        'wp_currency.enable_scrape_foreign_rate',
        'wp_currency.enable_scrape_local_reference',
    ];

    private CurrencyAdminDelegator $currencyAdminDelegator;
    private CurrencyService $currencyService;
    private BulkCurrencyRevertTaskService $bulkCurrencyRevertTaskService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;
    private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository;
    private AdmForeignCurrencyDailyRateRepository $admForeignCurrencyDailyRateRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;
    private OprProductRepository $oprProductRepository;
    private MstStoreProductRepository $mstStoreProductRepository;
    private UnionLogCurrencyRepository $unionLogCurrencyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyAdminDelegator = $this->app->make(CurrencyAdminDelegator::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->bulkCurrencyRevertTaskService = $this->app->make(BulkCurrencyRevertTaskService::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
        $this->admForeignCurrencyRateRepository = $this->app->make(AdmForeignCurrencyRateRepository::class);
        $this->admForeignCurrencyDailyRateRepository = $this->app->make(AdmForeignCurrencyDailyRateRepository::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
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
    public function addSandboxCurrencyPaid_正常登録()
    {
        // Setup
        // 通貨管理情報を登録
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        $trigger = new Trigger('debugPurchased', '', '', '');

        // Exercise
        /** @var UsrCurrencyPaid $usrCurrencyPaid */
        $usrCurrencyPaid = $this->currencyAdminDelegator->addSandboxCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'JPY',
            '100',
            2,
            101,
            'debug_1_123456789',
            $trigger
        );

        // Verify
        $this->assertEquals(1, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(2, $usrCurrencyPaid->left_amount);
        $this->assertEquals(100, $usrCurrencyPaid->purchase_price);
        $this->assertEquals(2, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals(50, $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals('JPY', $usrCurrencyPaid->currency_code);
        $this->assertEquals('debug_1_123456789', $usrCurrencyPaid->receipt_unique_id);
        $this->assertTrue((bool)$usrCurrencyPaid->is_sandbox);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrCurrencyPaid->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrCurrencyPaid->billing_platform);
    }

    #[Test]
    public function getCurrencySummary_正常取得()
    {
        // Setup
        $this->usrCurrencySummaryRepository
            ->insertCurrencySummary('1', 100, 50);

        // Exercise
        /** UsrCurrencySummaryEntity  */
        $usrCurrencySummaryEntity = $this->currencyAdminDelegator
            ->getCurrencySummary('1');

        // Verify
        $this->assertEquals(1, $usrCurrencySummaryEntity->usr_user_id);
        $this->assertEquals(0, $usrCurrencySummaryEntity->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummaryEntity->paid_amount_google);
        $this->assertEquals(100, $usrCurrencySummaryEntity->free_amount);
    }

    #[Test]
    public function getCurrencySummary_null取得()
    {
        // Exercise
        /** UsrCurrencySummaryEntity  */
        $usrCurrencySummaryEntity = $this->currencyAdminDelegator
            ->getCurrencySummary('999');

        // Verify
        $this->assertNull($usrCurrencySummaryEntity);
    }

    #[Test]
    public function revertCurrencyFromLog＿正常実行チェック_全返却(): void
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
        $revertHistoryIds = $this->currencyAdminDelegator
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                400
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
        $logRevertPaidLog = $logRevertPaidLogs->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaid->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaid->id, $logRevertPaidLog->revert_log_currency_paid_id);

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
    public function revertCurrencyFromLog＿正常実行チェック_一部返却(): void
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
        $revertHistoryIds = $this->currencyAdminDelegator
            ->revertCurrencyFromLog(
                '1',
                $logCurrencyPaidIds,
                $logCurrencyFreeIds,
                'comment',
                300
            );

        // Verify
        // 無償一次通貨の残高が戻っていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(0, $usrCurrencyFree->ingame_amount);
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
        $this->assertEquals(230, $usrCurrencySummary->free_amount);

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
        $this->assertEquals(-230, $logCurrencyRevertHistory->log_change_free_amount);
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
        $logRevertPaidLog = $logRevertPaidLogs->first();
        $this->assertEquals('1', $logRevertPaidLog->usr_user_id);
        $this->assertEquals($logCurrencyRevertHistory->id, $logRevertPaidLog->log_currency_revert_history_id);
        $this->assertEquals($logCurrencyPaid->id, $logRevertPaidLog->log_currency_paid_id);
        $this->assertEquals($revertLogCurrencyPaid->id, $logRevertPaidLog->revert_log_currency_paid_id);

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
    public function getYearOptions_取得(): void
    {
        // setUp
        $this->setupTestData();
        // 現在時刻を2025年に固定
        $this->setTestNow(Carbon::create(2025));

        // Exercise
        $results = $this->currencyAdminDelegator
            ->getYearOptions();

        // Verify
        $expected = [
            '2022' => '2022',
            '2023' => '2023',
            '2024' => '2024',
            '2025' => '2025'
        ];
        $this->assertSame($expected, $results);
    }

    #[Test]
    public function makeExcelCurrencyBalanceAggregation_正常実行(): void
    {
        // setUp
        $this->setupTestData();

        // Exercise
        $excel = $this->currencyAdminDelegator
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
                false
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
    public function makeExcelCollaboAggregation_正常実行(): void
    {
        // Setup
        //   コラボデータのログを格納
        //   集計対象、g-1, s-1
        //   コラボ期間(JST): 2023-01-01 00:00:00 〜 2023-01-31 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-01 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -120,
            triggerType: 'gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 23:59:59+09:00',
        );
        $this->makeAdmForeignCurrencyRate();

        // Exercise
        $startAt = Carbon::create(2023, 1, 1, 0, 0, 0, 'Asia/Tokyo');
        $endAt = Carbon::create(2023, 1, 31, 23, 59, 59, 'Asia/Tokyo');
        $searchTriggers = [
            ['type' => 'gacha', 'ids' => ['g-1', 'g-1-2']],
            ['type' => 'shop', 'ids' => ['s-1', 's-1-2']],
        ];

        // Exercise
        $result = $this->currencyAdminDelegator
            ->makeExcelCollaboAggregation($startAt, $endAt, $searchTriggers, false);

        // Verify
        $collection = $result->collection();
        $this->assertEquals(7, $collection->count());

        $row1 = $collection->first(fn($row) => isset($row[1]) && $row[1] === 'JPY' and $row[2] === '2023-01');;
        $this->assertEquals('g-1', $row1[0]);
        $this->assertEquals('JPY', $row1[1]);
        $this->assertEquals('2023-01', $row1[2]);
        $this->assertEquals('1.00000000', $row1[3]);
        $this->assertEquals('1', $row1[4]);
        $this->assertEquals('100', $row1[5]);
        $this->assertEquals('100.00000000', $row1[6]);

        $row2 = $collection->first(fn($row) => isset($row[1]) && $row[1] === 'USD' and $row[2] === '2023-01');
        $this->assertEquals('g-1', $row2[0]);
        $this->assertEquals('USD', $row2[1]);
        $this->assertEquals('2023-01', $row2[2]);
        $this->assertEquals('1.00000000', $row2[3]);
        $this->assertEquals('150.580000', $row2[4]);
        $this->assertEquals('120', $row2[5]);
        $this->assertEquals('18069.60000000', $row2[6]);
    }

    #[Test]
    public function makeCsvBulkLogCurrencyRevertSearch_正常実行(): void
    {
        // Setup
        //   コラボデータのログを格納
        //   集計対象：g-1
        //   コラボ期間(JST): 2023-01-01 00:00:00 〜 2023-01-31 23:59:59
        // 集計対象ガチャ
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'JPY',
            changeAmount: -100,
            triggerType: 'Gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-01 00:00:00+09:00',
        );
        $this->makeLogCurrencyPaidRecord(
            query: LogCurrencyPaid::QUERY_UPDATE,
            pricePerAmount: '1',
            currencyCode: 'USD',
            changeAmount: -120,
            triggerType: 'Gacha',
            triggerId: 'g-1',
            triggerName: 'コラボ1',
            createdAtJstStr: '2023-01-31 23:59:59+09:00',
        );

        // Exercise
        $startAt = CarbonImmutable::create(2023, 1, 1, 0, 0, 0, 'Asia/Tokyo');
        $endAt = CarbonImmutable::create(2023, 1, 31, 23, 59, 59, 'Asia/Tokyo');

        // Exercise
        $result = $this->currencyAdminDelegator
            ->makeCsvBulkLogCurrencyRevertSearch($startAt, $endAt, 'Gacha', 'g-1', false);

        // Verify
        $collection = $result->collection();
        $this->assertEquals(3, $collection->count());
        $resultArray = $collection->toArray();
        $this->assertEquals([
            'ユーザーID',
            'コンテンツ消費日時',
            '消費コンテンツタイプ',
            '消費コンテンツID',
            '消費コンテンツ名',
            'リクエストID',
            '消費有償一次通貨数(合計)',
            '消費無償一次通貨数(合計)',
            '有償一次通貨の消費ログID',
            '無償一次通貨の消費ログID',
        ],
            $resultArray[0]);

        $row1 = $resultArray[1];
        $this->assertEquals('100', $row1[0]);
        $this->assertEquals('2023-01-31 23:59:59', $row1[1]);
        $this->assertEquals('Gacha', $row1[2]);
        $this->assertEquals('g-1', $row1[3]);
        $this->assertEquals('コラボ1', $row1[4]);
        $this->assertEquals('-120', $row1[6]);
        $this->assertEquals('0', $row1[7]);
        $this->assertNotEmpty($row1[8]);
        $this->assertEmpty($row1[9]);

        $row2 = $resultArray[2];
        $this->assertEquals('100', $row2[0]);
        $this->assertEquals('2023-01-01 00:00:00', $row2[1]);
        $this->assertEquals('Gacha', $row2[2]);
        $this->assertEquals('g-1', $row2[3]);
        $this->assertEquals('コラボ1', $row2[4]);
        $this->assertEquals('-100', $row2[6]);
        $this->assertEquals('0', $row2[7]);
        $this->assertNotEmpty($row2[8]);
        $this->assertEmpty($row2[9]);
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
        $exists = $this->currencyAdminDelegator
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
    public function scrapeForeignCurrencyRate_実行(): void
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
            $this->app->make(UsrCurrencyPaidRepository::class),
            $this->app->make(UsrCurrencyFreeRepository::class),
            $this->app->make(LogCurrencyPaidRepository::class),
            $this->app->make(LogCurrencyFreeRepository::class),
            $this->app->make(LogCurrencyRevertHistoryRepository::class),
            $this->app->make(LogCurrencyRevertHistoryPaidLogRepository::class),
            $this->app->make(LogCurrencyRevertHistoryFreeLogRepository::class),
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

        // CurrencyAdminDelegatorのモック生成
        $currencyAdminDelegatorMock = $this->createPartialMock(CurrencyAdminDelegator::class, ['scrapeForeignCurrencyRate']);
        $reflectedClassDelegator = new ReflectionClass(CurrencyAdminDelegator::class);
        $constructorDelegator = $reflectedClassDelegator->getConstructor();
        $constructorDelegator->invoke(
            $currencyAdminDelegatorMock,
            $this->currencyService,
            $currencyAdminServiceMock,
            $this->bulkCurrencyRevertTaskService,
        );

        // Exercise
        app()->instance(CurrencyAdminDelegator::class, $currencyAdminDelegatorMock);
        $currencyAdminDelegator = new CurrencyAdminDelegator(
            $this->currencyService,
            $currencyAdminServiceMock,
            $this->bulkCurrencyRevertTaskService,
        );
        $currencyAdminDelegator->scrapeForeignCurrencyRate($year, $month);

        // Verify
        $result = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth($year, $month);
        // 三件登録されていることをチェック
        $this->assertEquals(5, $result->count());

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
    public function useCurrency_実行チェック(): void
    {
        // Setup
        $this->currencyService->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);

        // Exercise
        $this->currencyAdminDelegator->useCurrency('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 99, new Trigger('', '', '', ''));

        // Verify
        // 無償一次通貨レコードの確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals(1, $usrCurrencyFree->ingame_amount);

        // サマリーの確認
        $usrCurrencySummary = $this->currencyAdminDelegator->getCurrencySummary('1');
        $this->assertEquals(1, $usrCurrencySummary->free_amount);
    }

    #[Test]
    public function usePaid_実行チェック(): void
    {
        // Setup
        $this->currencyService->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);
        //  有償一次通貨を追加
        $this->currencyService->addCurrencyPaid(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', '', '', '')
        );

        // Exercise
        $this->currencyAdminDelegator->usePaid('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 99, new Trigger('', '', '', ''));

        // Verify
        // 有償一次通貨レコードの確認
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals(1, $usrCurrencyPaid->left_amount);

        // サマリーの確認
        $usrCurrencySummary = $this->currencyAdminDelegator->getCurrencySummary('1');
        $this->assertEquals(1, $usrCurrencySummary->paid_amount_apple);
    }

    #[Test]
    public function getOprProductById_取得(): void
    {
        // Setup
        $id = '1';
        $this->insertOptProduct('1', 0, '1-1', 10);

        // Exercise
        $result = $this->currencyAdminDelegator
            ->getOprProductById($id);

        // Verify
        $this->assertEquals('1', $result->id);
        $this->assertEquals('1-1', $result->mst_store_product_id);
        $this->assertEquals(10, $result->paid_amount);
    }

    #[Test]
    public function getMstStoreProductById_取得(): void
    {
        // Setup
        $id = '1-1';
        $this->insertMstStoreProduct('1-1', 0, 'ap-1', 'gg-1');

        // Exercise
        $result = $this->currencyAdminDelegator
            ->getMstStoreProductById($id);

        // Verify
        $this->assertEquals('1-1', $result->id);
        $this->assertEquals(0, $result->release_key);
        $this->assertEquals('ap-1', $result->product_id_ios);
        $this->assertEquals('gg-1', $result->product_id_android);
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
                'month' => '1',
                'currency' => 'US Dollar',
                'currency_name' => '米ドル',
                'currency_code' => 'USD',
                'tts' => '151.58',
                'ttb' => '149.58',
                'ttm' => '150.58',
            ],
            [
                'id' => '2',
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
                'id' => '3',
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

    /**
     * テストデータを作成する
     *
     * @return void
     */
    private function setupTestData()
    {
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '1000',
            purchaseAmount: 100,
            pricePerAmount: '10',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2022-12-01 10:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            purchasePrice: '1000',
            purchaseAmount: 1000,
            pricePerAmount: '1',
            beforeAmount: 100,
            changeAmount: 1000,
            currentAmount: 1100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '200',
            currencyPaidId: '3',
            receiptUniqueId: 'receipt_unique_id_3',
            purchasePrice: '120',
            purchaseAmount: 100,
            pricePerAmount: '1.20000000',
            currencyCode: 'USD',
            changeAmount: 100,
            currentAmount: 100,
            createdAtJstStr: '2023-12-01 11:00:00'
        );
        $this->makeLogCurrencyPaidRecord(
            userId: '300',
            currencyPaidId: '4',
            receiptUniqueId: 'receipt_unique_id_4',
            purchasePrice: '100',
            purchaseAmount: 150,
            pricePerAmount: '0.66666666',
            currencyCode: 'EUR',
            changeAmount: 150,
            currentAmount: 150,
            createdAtJstStr: '2023-12-01 11:00:00'
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
    ): void
    {
        // 作成日の日時設定
        // $createdAtJstStrを日本時間として生成、UTC時間に変換する
        // 例:日本時間 2023-01-01 00:00:00 -> UTC 2022-12-31 15:00:00 としてcreated_at,updated_atに保存
        $now = Carbon::create($createdAtJstStr, CurrencyAdminService::CARBON_CREATE_TZ_JST);
        $now->setTimezone('UTC');
        $this->setTestNow($now);
        $this->logCurrencyPaidRepository->insertPaidLog(
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

    #[Test]
    public function collectFreeCurrency_無償一次通貨を回収する()
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
        $this->currencyAdminDelegator->collectFreeCurrency(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            'bonus',
            100,
            'collect free currency',
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
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_FREE_ADMIN, $logs->trigger_type);
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
        $this->assertEquals('', $logs->trigger_id);
        $this->assertEquals('', $logs->trigger_name);
        $this->assertEquals('collect free currency', $logs->trigger_detail);
    }

    #[Test]
    public function getCurrencyFree_無償一次通貨内訳の取得()
    {
        // Setup
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $actual = $this->currencyAdminDelegator->getCurrencyFree('1');

        // Verify
        $this->assertEquals(100, $actual->ingame_amount);
        $this->assertEquals(110, $actual->bonus_amount);
        $this->assertEquals(120, $actual->reward_amount);
    }

    #[Test]
    public function getConsumeLogCurrencyPaidAndFrees_有償無償ログの取得()
    {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'test01';
        // 有償のみのログ
        $this->setTestNow('2024-01-01 00:00:00');
        $this->addPaid('1', 100, 'unique1');
        $this->consumeCurrency('1',90, $triggerType, $triggerId, 'テストガチャ1');
        // 有償無償両方のログ
        $this->setTestNow('2024-01-02 00:00:00');
        $this->addPaid('2', 100, 'unique2');
        $this->addFree('2', 100);
        $this->consumeCurrency('2', 110, $triggerType, $triggerId, 'テストガチャ1');
        // 無償のみのログ
        $this->setTestNow('2024-01-31 23:59:59');
        $this->addFree('3', 100);
        $this->consumeCurrency('3',80, $triggerType, $triggerId, 'テストガチャ1');

        // Exercise
        $result = $this->currencyAdminDelegator->getConsumeLogCurrencyPaidAndFrees(
            '2024-01-01 00:00:00',
            '2024-01-31 23:59:59',
            $triggerType,
            $triggerId,
            null,
            false
        );

        // Verify
        // 結果比較がしやすいようにcreated_atで並び替えする
        $actual = $result->orderBy('created_at', 'desc')->get()->toArray();
        $this->assertCount(4, $actual);

        // usr_user_id と log_currency_type の降順ソート
        usort($actual, function ($a, $b) {
            if ($a['usr_user_id'] === $b['usr_user_id']) {
                return strcmp($b['log_currency_type'], $a['log_currency_type']);
            }
            return strcmp($b['usr_user_id'], $a['usr_user_id']);
        });

        $row1 = $actual[0];
        $this->assertEquals('3', $row1['usr_user_id']);
        $this->assertEquals($triggerType, $row1['trigger_type']);
        $this->assertEquals($triggerId, $row1['trigger_id']);
        $this->assertEquals('free', $row1['log_currency_type']);
        $this->assertEquals('-80', $row1['log_change_amount']);
        $this->assertEquals('0', $row1['log_change_amount_paid']);
        $this->assertEquals('-80', $row1['log_change_amount_free']);
        $row2 = $actual[1];
        $this->assertEquals('2', $row2['usr_user_id']);
        $this->assertEquals($triggerType, $row2['trigger_type']);
        $this->assertEquals($triggerId, $row2['trigger_id']);
        $this->assertEquals('paid', $row2['log_currency_type']);
        $this->assertEquals('-10', $row2['log_change_amount']);
        $this->assertEquals('-10', $row2['log_change_amount_paid']);
        $this->assertEquals('0', $row2['log_change_amount_free']);
        $row3 = $actual[2];
        $this->assertEquals('2', $row3['usr_user_id']);
        $this->assertEquals($triggerType, $row3['trigger_type']);
        $this->assertEquals($triggerId, $row3['trigger_id']);
        $this->assertEquals('free', $row3['log_currency_type']);
        $this->assertEquals('-100', $row3['log_change_amount']);
        $this->assertEquals('0', $row3['log_change_amount_paid']);
        $this->assertEquals('-100', $row3['log_change_amount_free']);
        $row4 = $actual[3];
        $this->assertEquals('1', $row4['usr_user_id']);
        $this->assertEquals($triggerType, $row4['trigger_type']);
        $this->assertEquals($triggerId, $row4['trigger_id']);
        $this->assertEquals('paid', $row4['log_currency_type']);
        $this->assertEquals('-90', $row4['log_change_amount']);
        $this->assertEquals('-90', $row4['log_change_amount_paid']);
        $this->assertEquals('0', $row4['log_change_amount_free']);
    }

    /**
     * 有償通貨を配布
     * @param string $usrUserId
     * @param int $addAmount
     * @param string $reciptId
     * @param bool $isSandbox
     * @return void
     */
    private function addPaid(
        string $usrUserId,
        int    $addAmount,
        string $reciptId,
        bool   $isSandbox = false,
    ): void
    {
        // 通貨管理の登録
        // 既にデータがある場合はそのデータが返ってくる
        $this->currencyService->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 有償一次通貨の登録　(ログも一緒)
        $this->currencyService->addCurrencyPaid(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $addAmount,
            'JPY',
            '100',
            101,
            $reciptId,
            $isSandbox,
            new Trigger('purchased', '1', '', 'add currency'),
        );
    }

    /**
     * 無償通貨を配布
     * @param string $usrUserId
     * @param int $addAmount
     * @return void
     */
    private function addFree(
        string $usrUserId,
        int    $addAmount,
    ): void
    {
        // 通貨管理の登録
        // 既にデータがある場合はそのデータが返ってくる
        $this->currencyService->createUser(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            0,
        );
        // 無償一次通貨の登録　(ログも一緒)
        $this->currencyService->addFree(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $addAmount,
            'ingame',
            new Trigger('purchased', '1', '', 'add currency'),
        );
    }

    /**
     * 通貨の消費を実行
     * @param string $usrUserId
     * @param int $consumeCount
     * @param string $triggerType
     * @param string $triggerId
     * @param string $triggerName
     * @return void
     */
    private function consumeCurrency(string $usrUserId, int $consumeCount, string $triggerType, string $triggerId, string $triggerName): void
    {
        // 通貨の消費
        $this->currencyService->useCurrency(
            $usrUserId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $consumeCount,
            new Trigger($triggerType, $triggerId, $triggerName, 'use currency'),
        );
    }

    // 一次通貨返却一括実行
    #[Test]
    public function registerBulkCurrencyRevertTask_一括通貨返却タスクを登録する()
    {
        // Setup
        $admUserId = 1;
        $fileName = 'test.csv';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $totalCount = 3;

        // Exercise
        $task = $this->currencyAdminDelegator->registerBulkCurrencyRevertTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            $totalCount,
        );

        // Verify
        $bulkCurrencyRevertTaskId = $task->id;
        $this->assertIsString($bulkCurrencyRevertTaskId);

        // レコードのチェック
        $result = AdmBulkCurrencyRevertTask::find($bulkCurrencyRevertTaskId);
        $this->assertNotNull($result);

        $this->assertEquals($bulkCurrencyRevertTaskId, $result->id);
        $this->assertEquals($admUserId, $result->adm_user_id);
        $this->assertEquals($fileName, $result->file_name);
        $this->assertEquals($revertCurrencyNum, $result->revert_currency_num);
        $this->assertEquals($comment, $result->comment);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Ready, $result->status);
        $this->assertEquals($totalCount, $result->total_count);
        $this->assertEquals(0, $result->success_count);
        $this->assertEquals(0, $result->error_count);
    }

    #[Test]
    public function registerBulkCurrencyRevertTaskTargets_タスクの処理対象を登録()
    {
        // Setup
        $bulkCurrencyRevertTaskId = 'bulkCurrencyRevertTaskId-1';
        $revertCurrencyNum = 50;
        $comment = 'comment-1';
        $chunkSize = 2;

        $expectedTargets = [
            [
                'usr_user_id' => 'user-1',
                'consumed_at' => '2021-01-01 00:00:00+09:00',
                'trigger_type' => 'trigger_type-1',
                'trigger_id' => 'trigger-1',
                'trigger_name' => 'trigger_name-1',
                'request_id' => 'request-1',
                'sum_log_change_amount_paid' => 100,
                'sum_log_change_amount_free' => 100,
                'log_currency_paid_ids' => '1,2,3',
                'log_currency_free_ids' => '1,2,3',
            ],
            [
                'usr_user_id' => 'user-2',
                'consumed_at' => '2021-01-02 00:00:00+09:00',
                'trigger_type' => 'trigger_type-2',
                'trigger_id' => 'trigger-2',
                'trigger_name' => 'trigger_name-2',
                'request_id' => 'request-2',
                'sum_log_change_amount_paid' => 200,
                'sum_log_change_amount_free' => 200,
                'log_currency_paid_ids' => '4,5,6',
                'log_currency_free_ids' => '4,5,6',
            ],
            [
                'usr_user_id' => 'user-3',
                'consumed_at' => '2021-01-03 00:00:00+09:00',
                'trigger_type' => 'trigger_type-3',
                'trigger_id' => 'trigger-3',
                'trigger_name' => 'trigger_name-3',
                'request_id' => 'request-3',
                'sum_log_change_amount_paid' => 300,
                'sum_log_change_amount_free' => 300,
                'log_currency_paid_ids' => '7,8,9',
                'log_currency_free_ids' => '7,8,9',
            ],
        ];

        // Exercise
        $targets = $this->currencyAdminDelegator->registerBulkCurrencyRevertTaskTargets(
            $bulkCurrencyRevertTaskId,
            $revertCurrencyNum,
            $comment,
            $expectedTargets,
            $chunkSize,
        );

        // Verify
        // 対象ユーザーのレコードのチェック
        $results = AdmBulkCurrencyRevertTaskTarget::orderBy('consumed_at')->get();
        $this->assertCount(count($expectedTargets), $results);

        foreach (collect($expectedTargets)->sortBy('consumed_at') as $index => $expected) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $results[$index]->adm_bulk_currency_revert_task_id);
            $this->assertEquals($revertCurrencyNum, $results[$index]->revert_currency_num);
            $this->assertEquals($expected['usr_user_id'], $results[$index]->usr_user_id);
            $this->assertEquals(
                CarbonImmutable::parse($expected['consumed_at'])->setTimezone('UTC')->format('Y-m-d H:i:s'),
                $results[$index]->consumed_at->setTimezone('UTC')->format('Y-m-d H:i:s')
            );
            $this->assertEquals($expected['trigger_type'], $results[$index]->trigger_type);
            $this->assertEquals($expected['trigger_id'], $results[$index]->trigger_id);
            $this->assertEquals($expected['trigger_name'], $results[$index]->trigger_name);
            $this->assertEquals($expected['request_id'], $results[$index]->request_id);
            $this->assertEquals($expected['sum_log_change_amount_paid'], $results[$index]->sum_log_change_amount_paid);
            $this->assertEquals($expected['sum_log_change_amount_free'], $results[$index]->sum_log_change_amount_free);
            $this->assertEquals($comment, $results[$index]->comment);

            // ログの照合
            $ids = explode(',', $expected['log_currency_paid_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $results[$index]->paidLogs->pluck('log_currency_paid_id')->sort()->values()->toArray()
            );

            $ids = explode(',', $expected['log_currency_free_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $results[$index]->freeLogs->pluck('log_currency_free_id')->sort()->values()->toArray()
            );

            $this->assertCount(0, $results[$index]->revertHistoryLogs);
        }

        // 戻り値のチェック
        $this->assertCount(count($expectedTargets), $targets);
        $targets = $targets->sortBy('consumed_at')->values();
        foreach ($expectedTargets as $index => $expected) {
            $this->assertEquals($bulkCurrencyRevertTaskId, $targets[$index]['adm_bulk_currency_revert_task_id']);
            $this->assertEquals($revertCurrencyNum, $targets[$index]['revert_currency_num']);
            $this->assertEquals($expected['usr_user_id'], $targets[$index]['usr_user_id']);
            $this->assertEquals(
                CarbonImmutable::parse($expected['consumed_at'])->format('Y-m-d H:i:s'),
                $targets[$index]['consumed_at']->setTimeZone('Asia/Tokyo')->format('Y-m-d H:i:s')
            );
            $this->assertEquals($expected['trigger_type'], $targets[$index]['trigger_type']);
            $this->assertEquals($expected['trigger_id'], $targets[$index]['trigger_id']);
            $this->assertEquals($expected['trigger_name'], $targets[$index]['trigger_name']);
            $this->assertEquals($expected['request_id'], $targets[$index]['request_id']);
            $this->assertEquals($expected['sum_log_change_amount_paid'], $targets[$index]['sum_log_change_amount_paid']);
            $this->assertEquals($expected['sum_log_change_amount_free'], $targets[$index]['sum_log_change_amount_free']);
            $this->assertEquals($comment, $targets[$index]['comment']);

            // ログの照合
            $ids = explode(',', $expected['log_currency_paid_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $targets[$index]->paidLogs->pluck('log_currency_paid_id')->sort()->values()->toArray()
            );

            $ids = explode(',', $expected['log_currency_free_ids']);
            $this->assertEquals(
                collect($ids)->sort()->values()->toArray(),
                $targets[$index]->freeLogs->pluck('log_currency_free_id')->sort()->values()->toArray()
            );

            $this->assertCount(0, $targets[$index]->revertHistoryLogs);
        }
    }

    #[Test]
    public function revertCurrencyFromBulkCurrencyRevertTaskTarget_対象データに対して通貨を返却する()
    {
        // Setup
        $userId = 'user-1';
        // 対象になるユーザーデータを作成
        $this->currencyService->registerCurrencySummary($userId, CurrencyConstants::OS_PLATFORM_IOS, 100);
        // 有償通貨の登録
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $amount = 100;
        $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('add', '', '', '')
        );
        // 有償通貨の消費
        $this->currencyService->useCurrency(
            $userId,
            $osPlatform,
            $billingPlatform,
            200,
            new Trigger(
                'unit_test',
                '1',
                '',
                ''
            )
        );

        // 返却データの作成
        $paidIds = implode(
            ',',
            LogCurrencyPaid::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );
        $freeIds = implode(
            ',',
            LogCurrencyFree::query()
                ->where('usr_user_id', $userId)
                ->where('trigger_type', 'unit_test')
                ->where('trigger_id', '1')
                ->pluck('id')
                ->toArray()
        );

        $admUserId = 1;
        $fileName = 'test.csv';
        $revertCurrencyNum = 150;
        $comment = 'comment-1';
        $chunkSize = 2;

        $expectedTargets = [
            [
                'usr_user_id' => $userId,
                'consumed_at' => '2021-01-01 00:00:00+09:00',
                'trigger_type' => 'unit_test',
                'trigger_id' => '1',
                'trigger_name' => '',
                'request_id' => 'request-1',
                'sum_log_change_amount_paid' => 50,
                'sum_log_change_amount_free' => 100,
                'log_currency_paid_ids' => $paidIds,
                'log_currency_free_ids' => $freeIds,
            ],
        ];
        $task = $this->currencyAdminDelegator->registerBulkCurrencyRevertTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            count($expectedTargets),
        );
        $targets = $this->currencyAdminDelegator->registerBulkCurrencyRevertTaskTargets(
            $task->id,
            $revertCurrencyNum,
            $comment,
            $expectedTargets,
            $chunkSize,
        );

        // Exercise
        $result = $this->currencyAdminDelegator->revertCurrencyFromBulkCurrencyRevertTaskTarget(
            $targets[0],
            $revertCurrencyNum,
            $comment,
        );

        // Verify
        // グルーピングされた返却データは1つになるため、返ってくるIDも1つ
        $this->assertCount(1, $result);

        // 返却されていることの確認
        // 返却結果の詳細はrevertCurrencyFromLogのテストなどで確認しているので、ここでは簡易的な確認のみ
        $summary = $this->currencyService->getCurrencySummary($userId);
        $this->assertEquals(100 + 100 - 200 + 150, $summary->getTotalAmount());
        $this->assertEquals(100, $summary->getTotalPaidAmount());
        $this->assertEquals(50, $summary->getFreeAmount());

        // ターゲットのステータスが完了になっていることの確認
        $target = AdmBulkCurrencyRevertTaskTarget::find($targets[0]->id);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Finished, $target->status);
    }

    #[Test]
    public function finishBulkCurrencyRevertTask_タスクを完了する()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 10,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        AdmBulkCurrencyRevertTaskTarget::factory()->count(6)->sequence(function ($sequence) use ($task) {
                return [
                    'seq_no' => $sequence->index + 1,
                    'adm_bulk_currency_revert_task_id' => $task->id,
                    'status' => AdmBulkCurrencyRevertTaskTargetStatus::Finished,
                ];
            })->create();
        AdmBulkCurrencyRevertTaskTarget::factory()->count(4)->sequence(function ($sequence) use ($task) {
                return [
                    'seq_no' => $sequence->index + 7,
                    'adm_bulk_currency_revert_task_id' => $task->id,
                    'status' => AdmBulkCurrencyRevertTaskTargetStatus::Error,
                ];
            })->create();

        // Exercise
        $this->currencyAdminDelegator->finishBulkCurrencyRevertTask($task->id);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Finished, $result->status);
        $this->assertEquals(10, $result->total_count);
        $this->assertEquals(6, $result->success_count);
        $this->assertEquals(4, $result->error_count);
    }

    #[Test]
    public function updateBulkCurrencyRevertTaskToError_タスクのステータスをエラーにする()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 10,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        AdmBulkCurrencyRevertTaskTarget::factory()->count(6)->sequence(function ($sequence) use ($task) {
            return [
                'seq_no' => $sequence->index + 1,
                'adm_bulk_currency_revert_task_id' => $task->id,
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Finished,
            ];
        })->create();
        AdmBulkCurrencyRevertTaskTarget::factory()->count(4)->sequence(function ($sequence) use ($task) {
            return [
                'seq_no' => $sequence->index + 7,
                'adm_bulk_currency_revert_task_id' => $task->id,
                'status' => AdmBulkCurrencyRevertTaskTargetStatus::Error,
            ];
        })->create();
        $error = new \Exception('Error occurred');
        $errorMessage = json_encode([
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);

        // Exercise
        $this->currencyAdminDelegator->updateBulkCurrencyRevertTaskToError($task->id, $error);

        // Verify
        $result = AdmBulkCurrencyRevertTask::find($task->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskStatus::Error, $result->status);
        $this->assertEquals(10, $result->total_count);
        $this->assertEquals(6, $result->success_count);
        $this->assertEquals(4, $result->error_count);
        $this->assertEquals($errorMessage, $result->error_message);
    }

    #[Test]
    public function updateBulkCurrencyRevertTaskTargetToError_ターゲットのステータスをエラーにする()
    {
        // Setup
        $task = AdmBulkCurrencyRevertTask::factory()->create([
            'status' => AdmBulkCurrencyRevertTaskStatus::Processing,
            'total_count' => 1,
            'success_count' => 0,
            'error_count' => 0,
        ]);
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create([
            'adm_bulk_currency_revert_task_id' => $task->id,
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Processing,
        ]);
        $error = new \Exception('Error occurred');
        $errorMessage = json_encode([
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);

        // Exercise
        $this->currencyAdminDelegator->updateBulkCurrencyRevertTaskTargetToError($target->id, $error);

        // Verify
        $result = AdmBulkCurrencyRevertTaskTarget::find($target->id);
        $this->assertNotNull($result);
        $this->assertEquals(AdmBulkCurrencyRevertTaskTargetStatus::Error, $result->status);
        $this->assertEquals($errorMessage, $result->error_message);
    }
}

