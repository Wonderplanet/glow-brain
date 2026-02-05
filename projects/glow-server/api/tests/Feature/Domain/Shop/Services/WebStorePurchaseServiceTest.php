<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Shop\Services\WebStorePurchaseService;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserProfile;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\LogStore;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class WebStorePurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebStorePurchaseService $webStorePurchaseService;

    public function setUp(): void
    {
        parent::setUp();
        $this->webStorePurchaseService = $this->app->make(WebStorePurchaseService::class);
    }

    public static function params_testIsDuplicateOrder()
    {
        return [
            '重複なし' => [
                'orderId' => 1,
                'createHistory' => false,
                'expected' => false,
            ],
            '重複あり' => [
                'orderId' => 1,
                'createHistory' => true,
                'expected' => true,
            ],
        ];
    }

    #[DataProvider('params_testIsDuplicateOrder')]
    public function testIsDuplicateOrder_重複を正しく判定できる(int $orderId, bool $createHistory, bool $expected): void
    {
        // Setup
        if ($createHistory) {
            UsrStoreProductHistory::factory()->create([
                'order_id' => $orderId,
            ]);
        }

        // Exercise
        $actual = $this->webStorePurchaseService->isDuplicateOrder($orderId);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    public function testFilterVirtualGoodItems_正常系_virtual_goodアイテムのみをフィルタできること(): void
    {
        // Setup
        $items = [
            [
                'sku' => 'sku_001',
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
            [
                'sku' => 'sku_002',
                'type' => 'game_key',
                'quantity' => 1,
            ],
            [
                'sku' => 'sku_003',
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);

        // Exercise
        $actual = $this->webStorePurchaseService->filterVirtualGoodItems($itemEntities);

        // Verify
        $this->assertCount(2, $actual);
        $this->assertSame('sku_001', $actual[0]->getSku());
        $this->assertSame('sku_003', $actual[1]->getSku());
    }

    public function testFilterVirtualGoodItems_異常系_virtual_goodが存在しない場合(): void
    {
        // Setup
        $items = [
            [
                'sku' => 'sku_001',
                'type' => 'game_key',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);

        $this->webStorePurchaseService->filterVirtualGoodItems($itemEntities);
    }

    public function testValidatePurchaseItems_正常系_商品が存在し購入制限に引っかからない場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        // mst_store_product作成
        $productIdWebstore = 'webstore_sku_001';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::DIAMOND->value,
            'purchasable_count' => 1,
        ]);

        $items = [
            [
                'sku' => $productIdWebstore,
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);
        $virtualGoodItems = $this->webStorePurchaseService->filterVirtualGoodItems($itemEntities);

        // Exercise & Verify: 例外が投げられないこと
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);

        // アサーションなしで正常終了すればOK
        $this->assertTrue(true);
    }

    public function testValidatePurchaseItems_異常系_mst_store_productが存在しない場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        $items = [
            [
                'sku' => 'non_existent_sku',
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);
        $virtualGoodItems = $this->webStorePurchaseService->filterVirtualGoodItems($itemEntities);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);

        // Exercise
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);
    }

    public function testValidatePurchaseItems_異常系_opr_productが存在しない場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        // mst_store_productのみ作成（opr_productなし）
        $productIdWebstore = 'webstore_sku_001';
        MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ]);

        $items = [
            [
                'sku' => $productIdWebstore,
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);
        $virtualGoodItems = $this->webStorePurchaseService->filterVirtualGoodItems($itemEntities);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND);

        // Exercise
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);
    }

    public function testProcessOrder_正常系_ダイヤモンド商品のアイテムが付与されること(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $this->setUsrUserId($usrUserId);

        $orderId = 111;
        $invoiceId = 'invoice_123';
        $currencyCode = 'JPY';
        $orderAmount = 999;
        $orderMode = 'default';
        $transactionId = 'transaction1';
        $platform = UserConstant::PLATFORM_WEBSTORE;
        $now = $this->fixTime();

        // mst_store_product作成
        $productIdWebstore = 'webstore_diamond_sku';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        $oprProduct = OprProduct::factory()->create([
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::DIAMOND->value,
            'purchasable_count' => 1,
            'paid_amount' => 500,
        ])->toEntity();

        UsrUserProfile::factory()->create(['usr_user_id' => $usrUserId]);
        UsrWebstoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'country_code' => 'JP',
        ]);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
        ]);

        $this->createDiamond($usrUserId);

        $items = [
            [
                'sku' => $productIdWebstore,
                'type' => 'virtual_good',
                'quantity' => 1,
            ],
        ];

        $itemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);

        // Exercise
        $productSubIds = $this->webStorePurchaseService->processOrder(
            $usrUserId,
            $orderId,
            $invoiceId,
            $currencyCode,
            $orderAmount,
            $orderMode,
            $itemEntities,
            $transactionId,
            $platform,
            $now
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        // 購入履歴が作成されていること
        $this->assertDatabaseHas('usr_store_product_histories', [
            'usr_user_id' => $usrUserId,
            'order_id' => $orderId,
        ]);

        // usr_store_productsの購入回数がカウントアップされていること
        $this->assertDatabaseHas('usr_store_products', [
            'usr_user_id' => $usrUserId,
            'product_sub_id' => $oprProduct->getId(),
            'purchase_count' => 1,
            'purchase_total_count' => 1,
        ]);

        $this->assertDatabaseHas('log_currency_paids', [
            'usr_user_id' => $usrUserId,
            'os_platform' => CurrencyConstants::OS_PLATFORM_WEBSTORE,
            'billing_platform' => CurrencyConstants::PLATFORM_WEBSTORE,
            'receipt_unique_id' => $orderId,
            'is_sandbox' => 0,
            'purchase_price' => (string)$orderAmount,
            'purchase_amount' => $oprProduct->getPaidAmount(),
        ]);

        // log_storesにレコードが追加されていることを確認
        $logStore = LogStore::query()
            ->where('usr_user_id', $usrUserId)
            ->where('receipt_unique_id', (string)$orderId)
            ->first();
        $this->assertNotNull($logStore, 'log_storesにレコードが存在すること');
        $this->assertEquals($usrUserId, $logStore->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_WEBSTORE, $logStore->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_WEBSTORE, $logStore->billing_platform);
        $this->assertEquals($productIdWebstore, $logStore->platform_product_id);
        $this->assertEquals($mstStoreProduct->getId(), $logStore->mst_store_product_id);
        $this->assertEquals($oprProduct->getId(), $logStore->product_sub_id);
        $this->assertEquals($orderAmount, (float)$logStore->purchase_price); // DECIMAL型なので数値として比較
        $this->assertEquals($currencyCode, $logStore->currency_code);
        $this->assertEquals((string)$orderId, $logStore->receipt_unique_id);
        $this->assertEquals($oprProduct->getPaidAmount(), $logStore->paid_amount);
        $this->assertEquals(0, $logStore->is_sandbox);
        $this->assertEquals('webstore_purchased', $logStore->trigger_type);
        $this->assertEquals($oprProduct->getId(), $logStore->trigger_id);

        // raw_receiptがJSON形式で正しく記録されていることを確認
        $rawReceipt = json_decode($logStore->raw_receipt, true);
        $this->assertIsArray($rawReceipt);
        $this->assertEquals($orderId, $rawReceipt['order_id']);
        $this->assertEquals($invoiceId, $rawReceipt['invoice_id']);
        $this->assertEquals($transactionId, $rawReceipt['transaction_id']);

        $usrCurrencySummaryEntity = $this->getDiamond($usrUserId);
        $this->assertEquals($usrCurrencySummaryEntity->getPaidAmountShare(), $usrCurrencySummaryEntity->getPaidAmountApple());
        $this->assertEquals($usrCurrencySummaryEntity->getPaidAmountShare(), $usrCurrencySummaryEntity->getPaidAmountGoogle());
        $this->assertEquals($oprProduct->getPaidAmount(), $usrCurrencySummaryEntity->getPaidAmountShare());

        $this->assertCount(1, $productSubIds);
        $this->assertEquals($productSubIds->first(), $oprProduct->getId());
    }
}
