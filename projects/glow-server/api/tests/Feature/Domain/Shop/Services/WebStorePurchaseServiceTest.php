<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstPackContent;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Shop\Services\WebStorePurchaseService;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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

        // 実働環境では有償、無償分けて上限チェックしており、有償通貨と無償通貨でそれぞれ投げられる例外が異なる
        // 実装側もそれぞれの例外を補足しており、falseでは投げられる例外が異なり正常に例外を補足できず
        // 想定外の結果になるのでこのテストではfalseに戻す
        Config::set('wp_currency.store.separate_currency_limit_check', true);
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
        $this->createDiamond($usrUserId);

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

    public function testValidatePurchaseItems_正常系_パック商品でコイン_アイテム_有償通貨が上限内の場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();
        $coinMaxAmount = 999999;
        $itemMaxAmount = 9999;
        $packCoinAmount = 10000;
        $packItemAmount = 10;
        $packFreeDiamondAmount = 1000;

        // 各リソースの上限設定
        MstConfig::factory()->create(['key' => MstConfigConstant::USER_COIN_MAX_AMOUNT, 'value' => (string)$coinMaxAmount]);
        MstConfig::factory()->create(['key' => MstConfigConstant::USER_ITEM_MAX_AMOUNT, 'value' => (string)$itemMaxAmount]);

        // ユーザーのリソース所持数を設定（獲得後に上限ちょうどになる値）
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => $coinMaxAmount - $packCoinAmount,
        ]);

        // 無償通貨の上限: 999999999（config/wp_currency.php より）
        $maxFreeDiamond = 999999999;
        $this->createDiamond($usrUserId, $maxFreeDiamond - $packFreeDiamondAmount);

        $mstItemId = 'item1';
        MstItem::factory()->create(['id' => $mstItemId]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $itemMaxAmount - $packItemAmount,
        ]);

        // mst_store_product作成
        $productIdWebstore = 'webstore_pack_all_001';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        // mst_pack作成
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $mstStoreProduct->getId(),
            'sale_condition' => null,
            'cost_type' => MstPackCostType::CASH->value,
        ])->toEntity();

        // mst_pack_content作成（コイン、アイテム、無償通貨を含むパック）
        MstPackContent::factory()->createMany([
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::COIN->value,
                'resource_id' => '',
                'resource_amount' => $packCoinAmount,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => $mstItemId,
                'resource_amount' => $packItemAmount,
            ],
            [
                'mst_pack_id' => $mstPack->getId(),
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => '',
                'resource_amount' => $packFreeDiamondAmount,
            ]
        ]);

        // opr_product作成
        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::PACK->value,
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

        // Exercise & Verify: 例外が投げられないこと（獲得後にちょうど上限）
        // コイン: 989999 + 10000 = 999999（上限）
        // アイテム: 9989 + 10 = 9999（上限）
        // 無償通貨: (999999999 - 1000) + 1000 = 999999999（上限）
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);

        // アサーションなしで正常終了すればOK
        $this->assertTrue(true);
    }

    public function testValidatePurchaseItems_異常系_パック商品でアイテムが上限を超える場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        $itemMaxAmount = 9999;
        $packItemAmount = 10;
        MstConfig::factory()->create(['key' => MstConfigConstant::USER_ITEM_MAX_AMOUNT, 'value' => (string)$itemMaxAmount]);

        $mstItemId = 'item1';
        MstItem::factory()->create([
            'id' => $mstItemId,
        ]);

        // ユーザーのアイテム所持数を設定（上限9999、現在9990個所持、10個追加で10000 = 上限+1）
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $itemMaxAmount - $packItemAmount + 1,
        ]);

        // mst_store_product作成
        $productIdWebstore = 'webstore_pack_002';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        // mst_pack作成
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $mstStoreProduct->getId(),
            'sale_condition' => null,
            'cost_type' => MstPackCostType::CASH->value,
        ])->toEntity();

        // mst_pack_content作成（アイテム10個を含むパック、9990 + 10 = 10000 = 上限+1で上限超過）
        MstPackContent::factory()->create([
            'mst_pack_id' => $mstPack->getId(),
            'resource_type' => RewardType::ITEM->value,
            'resource_id' => $mstItemId,
            'resource_amount' => $packItemAmount,
        ]);

        // opr_product作成
        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::PACK->value,
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

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);

        // Exercise
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);
    }

    public function testValidatePurchaseItems_異常系_パック商品でコインが上限を超える場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        $coinMaxAmount = 999999;
        $packCoinAmount = 10000;
        MstConfig::factory()->create(['key' => MstConfigConstant::USER_COIN_MAX_AMOUNT, 'value' => (string)$coinMaxAmount]);

        // ユーザーのコイン所持数を設定（上限999999、現在990000個所持、10000個追加で1000000 = 上限+1）
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'coin' => $coinMaxAmount - $packCoinAmount + 1,
        ]);

        // mst_store_product作成
        $productIdWebstore = 'webstore_pack_coin_002';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        // mst_pack作成
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $mstStoreProduct->getId(),
            'sale_condition' => null,
            'cost_type' => MstPackCostType::CASH->value,
        ])->toEntity();

        // mst_pack_content作成（コイン10000個を含むパック、990000 + 10000 = 1000000 = 上限+1で上限超過）
        MstPackContent::factory()->create([
            'mst_pack_id' => $mstPack->getId(),
            'resource_type' => RewardType::COIN->value,
            'resource_id' => '',
            'resource_amount' => $packCoinAmount,
        ]);

        // opr_product作成
        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::PACK->value,
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

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);

        // Exercise
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);
    }

    public function testValidatePurchaseItems_異常系_パック商品で無償通貨が上限を超える場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        // 無償通貨の上限: 999999999（config/wp_currency.php より）
        $maxFreeDiamond = 999999999;
        $packFreeDiamondAmount = 1000;
        // ユーザーの無償通貨を設定（上限999999999、現在999999000個所持、1000個追加で1000000000 = 上限+1）
        $this->createDiamond($usrUserId, $maxFreeDiamond - $packFreeDiamondAmount + 1);

        // mst_store_product作成
        $productIdWebstore = 'webstore_pack_diamond_002';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        // mst_pack作成
        $mstPack = MstPack::factory()->create([
            'product_sub_id' => $mstStoreProduct->getId(),
            'sale_condition' => null,
            'cost_type' => MstPackCostType::CASH->value,
        ])->toEntity();

        // mst_pack_content作成（無償通貨1000個を含むパック、999999000 + 1000 = 1000000000 = 上限+1で上限超過）
        MstPackContent::factory()->create([
            'mst_pack_id' => $mstPack->getId(),
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => '',
            'resource_amount' => $packFreeDiamondAmount,
        ]);

        // opr_product作成
        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::PACK->value,
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

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);

        // Exercise
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodItems, $usrUserId, $now);
    }

    public function testValidatePurchaseItems_異常系_ダイヤモンド商品で有償通貨が上限を超える場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $now = $this->fixTime();

        // 有償通貨の上限: 999999999（config/wp_currency.php より）
        $maxPaidDiamond = 999999999;
        $paidAmount = 1000;
        // ユーザーの有償通貨を設定（上限999999999、現在999999000個所持、1000個追加で1000000000 = 上限+1）
        $this->createDiamond($usrUserId, 0, 0, 0, $maxPaidDiamond - $paidAmount + 1);

        // mst_store_product作成
        $productIdWebstore = 'webstore_diamond_002';
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'product_id_webstore' => $productIdWebstore,
        ])->toEntity();

        // opr_product作成（有償通貨1000個を含むダイヤモンド商品、999999000 + 1000 = 1000000000 = 上限+1で上限超過）
        OprProduct::factory()->create([
            'id' => $mstStoreProduct->getId(),
            'mst_store_product_id' => $mstStoreProduct->getId(),
            'product_type' => ProductType::DIAMOND->value,
            'purchasable_count' => 1,
            'paid_amount' => $paidAmount,
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
        $this->expectExceptionCode(ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED);

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
