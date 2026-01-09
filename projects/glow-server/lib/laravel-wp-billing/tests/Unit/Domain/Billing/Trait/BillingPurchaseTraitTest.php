<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Trait;


use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Services\BillingAdminService;
use WonderPlanet\Domain\Billing\Services\BillingService;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Tests\Traits\Domain\Currency\DataFixtureTrait;

class BillingPurchaseTraitTest extends TestCase
{
    use RefreshDatabase;
    use DataFixtureTrait;
    use FakeStoreReceiptTrait;

    private BillingService $billingService;
    private BillingAdminService $billingAdminService;
    private MstStoreProductRepository $mstStoreProductRepository;
    private OprProductRepository $oprProductRepository;
    private UsrStoreInfoRepository $usrStoreInfoRepository;
    private CurrencyDelegator $currencyDelegator;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository;
    private LogStoreRepository $logStoreRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->billingService = $this->app->make(BillingService::class);
        $this->billingAdminService = $this->app->make(BillingAdminService::class);
        $this->mstStoreProductRepository = $this->app->make(MstStoreProductRepository::class);
        $this->oprProductRepository = $this->app->make(OprProductRepository::class);
        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrStoreProductHistoryRepository = $this->app->make(UsrStoreProductHistoryRepository::class);
        $this->logStoreRepository = $this->app->make(LogStoreRepository::class);
    }

    #[Test]
    #[DataProvider('executePurchaseData')]
    public function executePurchase_billingServiceからの実行(
        string $osPlatform,
        string $billingPlatform,
        string $storeProductId
    ): void {
        // Setup
        //  各パラメータ
        $userId = '100';
        $deviceId = 'device1';
        $mstStoreProductId = '1-1';
        $productSubId = '1';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $currencyCode = 'JPY';
        $trigger = new Trigger('purchased', 'product1', 'trigger product1 name', 'sample details');
        $loggingProductSubName = 'product1 name';
        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        $callback = function () use (&$actualCallbackFlg) {
            $actualCallbackFlg = true;
        };
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $paidAmount = 10;
        $this->insertMstStoreProduct($mstStoreProductId, 0, 'ap-1', 'gg-1');
        $this->insertOptProduct($productSubId, 0, $mstStoreProductId, $paidAmount);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );
        $this->billingService->setStoreInfo($userId, 30, '2024-01-01 00:00:00');
        $receipt = $this->makeFakeStoreReceiptNoSandbox('store_product1');
        $receiptUniqueId = $receipt->getUnitqueId();
        $bundleId = $receipt->getBundleId();
        $purchaseToken = $receipt->getPurchaseToken();
        $receiptStr = $receipt->getReceipt();

        // Exercise
        $this->callMethod(
            $this->billingService,
            'executePurchase',
            [
                $userId,
                $osPlatform,
                $billingPlatform,
                $deviceId,
                $storeProductId,
                $mstStoreProductId,
                $productSubId,
                $purchasePrice,
                $rawPriceString,
                $vipPoint,
                $currencyCode,
                $receiptUniqueId,
                $bundleId,
                $purchaseToken,
                $receiptStr,
                $trigger,
                $loggingProductSubName,
                $callback,
                $isSandbox
            ]
        );

        // Verify
        //  購入した数だけ所持数が増えていること
        $currencySummary = $this->currencyDelegator->getCurrencySummary($userId);
        $this->assertEquals($paidAmount, $currencySummary->getTotalPaidAmount());
        if ($billingPlatform === CurrencyConstants::PLATFORM_APPSTORE) {
            // AppleStore購入数チェック
            $this->assertEquals($paidAmount, $currencySummary->paid_amount_apple);
        } else {
            // GooglePlay購入数チェック
            $this->assertEquals($paidAmount, $currencySummary->paid_amount_google);
        }


        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals($osPlatform, $usrCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals('100.000000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        //  ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform($receiptUniqueId, $billingPlatform);
        $this->assertEquals($userId, $usrStoreProductHistory->usr_user_id);
        $this->assertEquals($osPlatform, $usrStoreProductHistory->os_platform);
        $this->assertEquals($billingPlatform, $usrStoreProductHistory->billing_platform);
        $this->assertEquals($productSubId, $usrStoreProductHistory->product_sub_id);
        $this->assertEquals($storeProductId, $usrStoreProductHistory->platform_product_id);
        $this->assertEquals($mstStoreProductId, $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals($currencyCode, $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('10.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals($vipPoint, $usrStoreProductHistory->vip_point);
        $this->assertEquals($deviceId, $usrStoreProductHistory->device_id);
        $this->assertEquals(30, $usrStoreProductHistory->age);

        //  ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertEquals($vipPoint, $usrStoreInfo->total_vip_point);

        //  ログが追加されていること
        //   log_store
        $logStore = $this->logStoreRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $logStore->usr_user_id);
        $this->assertEquals($osPlatform, $logStore->os_platform);
        $this->assertEquals($billingPlatform, $logStore->billing_platform);
        $this->assertEquals($deviceId, $logStore->device_id);
        $this->assertEquals(30, $logStore->age);
        $this->assertEquals($storeProductId, $logStore->platform_product_id);
        $this->assertEquals($mstStoreProductId, $logStore->mst_store_product_id);
        $this->assertEquals($productSubId, $logStore->product_sub_id);
        $this->assertEquals($loggingProductSubName, $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('10.00000000', $logStore->price_per_amount);
        $this->assertEquals($vipPoint, $logStore->vip_point);
        $this->assertEquals($currencyCode, $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($usrStoreProductHistory->receipt_bundle_id, $logStore->receipt_bundle_id);
        //    レシート情報はjson_decodeしてpayloadとstoreの文字列をチェックする
        $receipt = json_decode($logStore->raw_receipt, true);
        $this->assertEquals('ThisIsFakeReceiptData', $receipt['Payload']);
        $this->assertEquals('fake', $receipt['Store']);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals('10', $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('purchased', $logStore->trigger_type);
        $this->assertEquals('product1', $logStore->trigger_id);
        $this->assertEquals('trigger product1 name', $logStore->trigger_name);
        $this->assertEquals('sample details', $logStore->trigger_detail);

        //  log_currency_paid
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', $userId)->first();
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals($osPlatform, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($usrCurrencyPaid->receipt_unique_id, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(10, $logCurrencyPaid->current_amount);
        $this->assertEquals('purchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals('product1', $logCurrencyPaid->trigger_id);
        $this->assertEquals('trigger product1 name', $logCurrencyPaid->trigger_name);
        $this->assertEquals('sample details', $logCurrencyPaid->trigger_detail);
    }

    #[Test]
    #[DataProvider('executePurchaseData')]
    public function executePurchase_billingAdminServiceからの実行(
        string $osPlatform,
        string $billingPlatform,
        string $storeProductId
    ): void {
        // Setup
        //  各パラメータ
        $userId = '100';
        $deviceId = 'grant by tool';
        $mstStoreProductId = '1-1';
        $productSubId = '1';
        $purchasePrice = '100';
        $rawPriceString = '¥100';
        $vipPoint = 21;
        $currencyCode = 'JPY';
        $triggerDetail = "product_id: {$storeProductId}, billing_platform: {$billingPlatform}, "
            . "mst_product_id: {$mstStoreProductId}";
        $trigger = new Trigger('adminPurchased', $productSubId, $productSubId, $triggerDetail);
        $loggingProductSubName = $productSubId;
        //  コールバック実行の確認用フラグ
        $actualCallbackFlg = false;
        $callback = function () use (&$actualCallbackFlg) {
            $actualCallbackFlg = true;
        };
        $isSandbox = false;
        //  購入商品のマスタデータを作成
        $paidAmount = 10;
        $this->insertMstStoreProduct($mstStoreProductId, 0, 'ap-1', 'gg-1');
        $this->insertOptProduct($productSubId, 0, $mstStoreProductId, $paidAmount);
        //  ユーザーデータ作成
        $this->currencyDelegator->createUser(
            $userId,
            $osPlatform,
            $billingPlatform,
            0,
        );
        $this->billingService->setStoreInfo($userId, 30, '2024-01-01 00:00:00');
        $receiptUniqueId = 'receipt_unique_admin';
        $bundleId = StoreUtility::getBundleIdOrPackageName($isSandbox, $billingPlatform);
        $purchaseToken = 'purchase_token_admin';
        $receiptStr = $this->callMethod(
            $this->billingAdminService,
            'makeReceiptAdmin',
            ['GrantByTool']
        );

        // Exercise
        $this->callMethod(
            $this->billingAdminService,
            'executePurchase',
            [
                $userId,
                $osPlatform,
                $billingPlatform,
                $deviceId,
                $storeProductId,
                $mstStoreProductId,
                $productSubId,
                $purchasePrice,
                $rawPriceString,
                $vipPoint,
                $currencyCode,
                $receiptUniqueId,
                $bundleId,
                $purchaseToken,
                $receiptStr,
                $trigger,
                $loggingProductSubName,
                $callback,
                $isSandbox
            ]
        );

        // Verify
        //  購入した数だけ所持数が増えていること
        $currencySummary = $this->currencyDelegator->getCurrencySummary($userId);
        $this->assertEquals($paidAmount, $currencySummary->getTotalPaidAmount());
        if ($billingPlatform === CurrencyConstants::PLATFORM_APPSTORE) {
            // AppleStore購入数チェック
            $this->assertEquals($paidAmount, $currencySummary->paid_amount_apple);
        } else {
            // GooglePlay購入数チェック
            $this->assertEquals($paidAmount, $currencySummary->paid_amount_google);
        }

        //  paidの管理レコードが追加されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals($osPlatform, $usrCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->left_amount);
        $this->assertEquals('100.000000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals($paidAmount, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $usrCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $usrCurrencyPaid->currency_code);
        $this->assertEquals($receiptUniqueId, $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $usrCurrencyPaid->is_sandbox);

        //  コールバックが動作していること
        $this->assertTrue($actualCallbackFlg);

        //  ストア購入履歴が登録されていること
        $usrStoreProductHistory = $this->usrStoreProductHistoryRepository
            ->findByReceiptUniqueIdAndBillingPlatform($receiptUniqueId, $billingPlatform);
        $this->assertEquals($userId, $usrStoreProductHistory->usr_user_id);
        $this->assertEquals($osPlatform, $usrStoreProductHistory->os_platform);
        $this->assertEquals($billingPlatform, $usrStoreProductHistory->billing_platform);
        $this->assertEquals($productSubId, $usrStoreProductHistory->product_sub_id);
        $this->assertEquals($storeProductId, $usrStoreProductHistory->platform_product_id);
        $this->assertEquals($mstStoreProductId, $usrStoreProductHistory->mst_store_product_id);
        $this->assertEquals($currencyCode, $usrStoreProductHistory->currency_code);
        $this->assertEquals('100.000000', $usrStoreProductHistory->purchase_price);
        $this->assertEquals('10.00000000', $usrStoreProductHistory->price_per_amount);
        $this->assertEquals($vipPoint, $usrStoreProductHistory->vip_point);
        $this->assertEquals($deviceId, $usrStoreProductHistory->device_id);
        $this->assertEquals(30, $usrStoreProductHistory->age);

        //  ストア情報の累計購入額に加算されていること
        $usrStoreInfo = $this->billingService->getStoreInfo($userId);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertEquals($vipPoint, $usrStoreInfo->total_vip_point);

        //  ログが追加されていること
        //   log_store
        $logStore = $this->logStoreRepository->findByUserId($userId)[0];
        $this->assertEquals($userId, $logStore->usr_user_id);
        $this->assertEquals($osPlatform, $logStore->os_platform);
        $this->assertEquals($billingPlatform, $logStore->billing_platform);
        $this->assertEquals($deviceId, $logStore->device_id);
        $this->assertEquals(30, $logStore->age);
        $this->assertEquals($storeProductId, $logStore->platform_product_id);
        $this->assertEquals($mstStoreProductId, $logStore->mst_store_product_id);
        $this->assertEquals($productSubId, $logStore->product_sub_id);
        $this->assertEquals($loggingProductSubName, $logStore->product_sub_name);
        $this->assertEquals('100.000000', $logStore->purchase_price);
        $this->assertEquals('10.00000000', $logStore->price_per_amount);
        $this->assertEquals($vipPoint, $logStore->vip_point);
        $this->assertEquals($currencyCode, $logStore->currency_code);
        $this->assertEquals($receiptUniqueId, $logStore->receipt_unique_id);
        $this->assertEquals($usrStoreProductHistory->receipt_bundle_id, $logStore->receipt_bundle_id);
        //    レシート情報はjson_decodeしてpayloadとstoreの文字列をチェックする
        $receipt = json_decode($logStore->raw_receipt, true);
        $this->assertEquals('GrantByTool', $receipt['Payload']);
        $this->assertEquals('admin', $receipt['Store']);
        $this->assertEquals($rawPriceString, $logStore->raw_price_string);
        $this->assertEquals('10', $logStore->paid_amount);
        $this->assertEquals(0, $logStore->free_amount);
        $this->assertEquals('adminPurchased', $logStore->trigger_type);
        $this->assertEquals($productSubId, $logStore->trigger_id);
        $this->assertEquals($productSubId, $logStore->trigger_name);
        $this->assertEquals($triggerDetail, $logStore->trigger_detail);

        //  log_currency_paid
        $logCurrencyPaid = LogCurrencyPaid::query()->where('usr_user_id', $userId)->first();
        $this->assertEquals($userId, $logCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $logCurrencyPaid->seq_no);
        $this->assertEquals($osPlatform, $logCurrencyPaid->os_platform);
        $this->assertEquals($billingPlatform, $logCurrencyPaid->billing_platform);
        $this->assertEquals($usrCurrencyPaid->id, $logCurrencyPaid->currency_paid_id);
        $this->assertEquals($usrCurrencyPaid->receipt_unique_id, $logCurrencyPaid->receipt_unique_id);
        $this->assertEquals($isSandbox, $logCurrencyPaid->is_sandbox);
        $this->assertEquals(LogCurrencyPaid::QUERY_INSERT, $logCurrencyPaid->query);
        $this->assertEquals('100.000000', $logCurrencyPaid->purchase_price);
        $this->assertEquals(10, $logCurrencyPaid->purchase_amount);
        $this->assertEquals('10.00000000', $logCurrencyPaid->price_per_amount);
        $this->assertEquals($vipPoint, $logCurrencyPaid->vip_point);
        $this->assertEquals($currencyCode, $logCurrencyPaid->currency_code);
        $this->assertEquals(0, $logCurrencyPaid->before_amount);
        $this->assertEquals(10, $logCurrencyPaid->change_amount);
        $this->assertEquals(10, $logCurrencyPaid->current_amount);
        $this->assertEquals('adminPurchased', $logCurrencyPaid->trigger_type);
        $this->assertEquals($productSubId, $logCurrencyPaid->trigger_id);
        $this->assertEquals($productSubId, $logCurrencyPaid->trigger_name);
        $this->assertEquals($triggerDetail, $logCurrencyPaid->trigger_detail);
    }

    /**
     * billingService、billingAdminService実行テスト共通のDataProvider
     *
     * @return array[]
     */
    public static function executePurchaseData(): array
    {
        return [
            'AppleStoreで購入' => [
                CurrencyConstants::OS_PLATFORM_IOS,
                CurrencyConstants::PLATFORM_APPSTORE,
                'ap-1'
            ],
            'GooglePlayで購入' => [
                CurrencyConstants::OS_PLATFORM_ANDROID,
                CurrencyConstants::PLATFORM_GOOGLEPLAY,
                'gg-1'
            ],
        ];
    }

    #[Test]
    public function verifyPurchaseStoreProduct_エラーなし(): void
    {
        // Setup
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $productId = 'productIdAndroid';
        $this->insertOptProduct('opr_product_1', 0, 'mst_store_product_id_1', 10);
        $this->insertMstStoreProduct('mst_store_product_id_1', 0, 'productIdApple', $productId);
        $mstStoreProduct = $this->mstStoreProductRepository
            ->findById('mst_store_product_id_1');
        $oprProduct = $this->oprProductRepository
            ->findById('opr_product_1');

        // Exercise
        $this->callMethod(
            $this->billingService,
            'verifyPurchaseStoreProduct',
            [
                $billingPlatform,
                $productId,
                $mstStoreProduct,
                $oprProduct
            ]
        );

        // Verify
        // 例外が発生しなければ成功とする
        $this->assertTrue(true);
    }

    #[Test]
    #[DataProvider('verifyPurchaseStoreProductExceptionData')]
    public function verifyPurchaseStoreProduct_例外チェック(
        string $productId,
        string $mstStoreProductId,
        string $registerMstStoreProductId,
        string $oprProductId,
        string $expectErrorMsg
    ): void {
        // Setup
        $billingPlatform = CurrencyConstants::PLATFORM_GOOGLEPLAY;
        $this->insertOptProduct('opr_product_1', 0, $registerMstStoreProductId, 10);
        $this->insertMstStoreProduct('mst_store_product_id_1', 0, 'productIdApple', 'productIdAndroid');
        $mstStoreProduct = $this->mstStoreProductRepository
            ->findById($mstStoreProductId);
        $oprProduct = $this->oprProductRepository
            ->findById($oprProductId);

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage($expectErrorMsg);
        $this->callMethod(
            $this->billingService,
            'verifyPurchaseStoreProduct',
            [
                $billingPlatform,
                $productId,
                $mstStoreProduct,
                $oprProduct
            ]
        );
    }

    /**
     * @return array[]
     */
    public static function verifyPurchaseStoreProductExceptionData(): array
    {
        return [
            'opr_productがnull' => [
                'productIdAndroid', // productId
                'mst_store_product_id_1', // $mstStoreProductId
                'mst_store_product_id_1', // $registerMstStoreProductId
                'opr_product_2', // $oprProductId
                'Billing-102: opr_product not found' // $expectErrorMsg
            ],
            'mst_store_productがnull' => [
                'productIdAndroid', // productId
                'mst_store_product_id_2', // $mstStoreProductId
                'mst_store_product_id_1', // $registerMstStoreProductId
                'opr_product_1', // $oprProductId
                'Billing-103: mst_store_product not found' // $expectErrorMsg
            ],
            'mstとoprのidが不一致' => [
                'productIdAndroid', // productId
                'mst_store_product_id_1', // $mstStoreProductId
                'mst_store_product_id_2', // $registerMstStoreProductId
                'opr_product_1', // $oprProductId
                'Billing-100: mst_store_product_id not match, mst:mst_store_product_id_1'
                . ' opr:mst_store_product_id_2' // $expectErrorMsg
            ],
            'mst_store_product_idとproductIdが不一致' => [
                'productIdAndroid2', // productId
                'mst_store_product_id_1', // $mstStoreProductId
                'mst_store_product_id_1', // $registerMstStoreProductId
                'opr_product_1', // $oprProductId
                'Billing-101: mst_store_product_id not match, mst:productIdAndroid'
                . ' billing:productIdAndroid2' // $expectErrorMsg
            ],
        ];
    }

    #[Test]
    public function addStoreInfoPaidPrice_累計購入金額の加算()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->callMethod(
            $this->billingService,
            'addStoreInfoPaidPrice',
            [
                '1',
                'JPY',
                '100'
            ]
        );

        // Verify
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(200, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function addStoreInfoPaidPrice_確認日がない場合は累計に加算されない()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 0, null);

        // Exercise
        $this->callMethod(
            $this->billingService,
            'addStoreInfoPaidPrice',
            [
                '1',
                'JPY',
                '100'
            ]
        );

        // Verify
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(0, $usrStoreInfo->paid_price);
        $this->assertNull($usrStoreInfo->renotify_at);
    }

    #[Test]
    public function addStoreInfoPaidPrice_ショップ情報がない場合のエラー()
    {
        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ErrorCode::SHOP_INFO_NOT_FOUND);
        $this->callMethod(
            $this->billingService,
            'addStoreInfoPaidPrice',
            [
                '1',
                'JPY',
                '100'
            ]
        );
    }

    #[Test]
    public function addStoreInfoPaidPrice_通貨が円ではない場合は無視する()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->callMethod(
            $this->billingService,
            'addStoreInfoPaidPrice',
            [
                '1',
                'USD',
                '100'
            ]
        );

        // Verify
        $usrStoreInfo = $this->billingService->getStoreInfo('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }
}
