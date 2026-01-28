<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Currency\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalAdminDelegator;
use WonderPlanet\Domain\Currency\Entities\CollectPaidCurrencyAdminTrigger;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyInternalAdminDelegatorTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyInternalAdminDelegator $currencyInternalAdminDelegator;
    private CurrencyService $currencyService;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;
    private LogCurrencyPaidRepository $logCurrencyPaidRepository;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;
    private LogCurrencyFreeRepository $logCurrencyFreeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyInternalAdminDelegator = $this->app->make(CurrencyInternalAdminDelegator::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
        $this->logCurrencyPaidRepository = $this->app->make(LogCurrencyPaidRepository::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
        $this->logCurrencyFreeRepository = $this->app->make(LogCurrencyFreeRepository::class);
    }

    #[Test]
    public function collectCurrencyPaid_正常実行(): void
    {
        // 回収後の所持通貨数がマイナス、freeAmountからも回収するように実行

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
        // apple通貨1000消費
        $this->currencyService->usePaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            $billingPlatform,
            1000,
            new Trigger(
                'consume',
                'test_trigger_id',
                'test_trigger_name',
                'test_trigger_detail'
            ),
        );

        // 回収処理実行用パラメータ
        $receiptUniqueId = 'COLLECT_BY_TOOL_TEST';
        $trigger = new CollectPaidCurrencyAdminTrigger('usr_store_product_history_id','trigger_detail_test');

        // Exercise
        $usrCurrencyPaid = $this->currencyInternalAdminDelegator
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
        //   回収レコードの減算チェック(left_amountがマイナスになっていること)
        //    left_amount(-950) = 購入(1050) - 消費(1000) - 回収(1000)
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals(-950, $usrCurrencyPaid->left_amount);
        $this->assertEquals($collectTargetReceiptUniqueId, $usrCurrencyPaid->receipt_unique_id);

        //  usrCurrencySummary
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId($userId);
        //   各プラットフォームの通貨チェック
        $this->assertEquals(-950, $usrCurrencySummary->getPaidAmountApple());
        $this->assertEquals(100, $usrCurrencySummary->getPaidAmountGoogle());
        $this->assertEquals(-850, $usrCurrencySummary->getTotalPaidAmount());

        //  logCurrencyPaidのチェック
        //   レコードが6件ある(購入3件、消費2件(2レコードから引き落とし)、回収1件)
        $logCurrencyPaids = $this->logCurrencyPaidRepository->findByUserId($userId);
        $this->assertCount(6, $logCurrencyPaids);
        //   回収ログのチェック
        $logCurrencyPaid = collect($logCurrencyPaids)->first(
            function ($row) use ($usrCurrencyPaid) {
                return $row->currency_paid_id === $usrCurrencyPaid->id
                    && $row->trigger_type === Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN;
            });
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals(3, $logCurrencyPaid->seq_no);
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals($receiptUniqueId, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_UPDATE, $logCurrencyPaid->query);
        $this->assertEquals('1000.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('1.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals(0, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(50, $logCurrencyPaid->before_amount);
        $this->assertEquals(-1 * $amount, $logCurrencyPaid->change_amount);
        $this->assertEquals(-950, $logCurrencyPaid->current_amount);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals(Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN, $logCurrencyPaid->trigger_type);
        $this->assertEquals('usr_store_product_history_id', $logCurrencyPaid->trigger_id);
        $this->assertEquals('', $logCurrencyPaid->trigger_name);
        $this->assertEquals('trigger_detail_test', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    public function collectCurrencyFreeByCollectPaid_回収実行(): void
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
        $this->currencyInternalAdminDelegator->collectFreeCurrencyByCollectPaid(
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
}
