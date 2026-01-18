<?php

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Shop\UseCases\ShopPurchaseUseCase;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class ShopPurchaseUseCaseErrorHandlingTest extends TestCase
{
    use FakeStoreReceiptTrait;

    private ShopPurchaseUseCase $shopPurchaseUseCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->shopPurchaseUseCase = $this->app->make(ShopPurchaseUseCase::class);
    }

    public static function params_test_購入処理で年齢に応じて課金限度額が変わっていることを確認()
    {
        return [
            '18歳 限度額なし 購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20060701,
                'beforeAge' => 18,
                'expectedAge' => 18,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 0, // 限度額がない年齢の場合は加算されないので0
                'beforeRenotifyAt' => null,
                'expectedRenotifyAt' => null,
            ],

            '17歳 限度額あり(20000円まで) 購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 120,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17歳 限度額あり(20000円まで) 限度ピッタリで購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 20000 - 120,
                'expectedPaidPrice' => 20000,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17歳 限度額あり(20000円まで) 初回で限度ピッタリで購入できる' => [
                'purchasePrice' => '20000.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 20000,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17歳 限度額あり(20000円まで) 1円でも超えたら購入できない' => [
                'purchasePrice' => '120.00',
                'errorCode' => ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 20000 - 120 + 1,
                'expectedPaidPrice' => 20001, // 実際には確認しない。メモ程度。
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17歳 限度額あり(20000円まで) 初回で限度額を超えて購入できない' => [
                'purchasePrice' => '99999.00',
                'errorCode' => ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 99999, // 実際には確認しない。メモ程度。
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17歳 限度額あり(20000円まで) リセットされて購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 17,
                'expectedAge' => 17,
                'beforePaidPrice' => 19999,
                'expectedPaidPrice' => 120,
                'beforeRenotifyAt' => '2024-06-30 15:00:00', // 累計課金額リセット
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],

            '15歳 限度額あり(5000円まで) 購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 120,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '15歳 限度額あり(5000円まで) 限度ピッタリで購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 5000 - 120,
                'expectedPaidPrice' => 5000,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '15歳 限度額あり(5000円まで) 初回で限度ピッタリで購入できる' => [
                'purchasePrice' => '5000.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 5000,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '15歳 限度額あり(5000円まで) 1円でも超えたら購入できない' => [
                'purchasePrice' => '120.00',
                'errorCode' => ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 5000 - 120 + 1,
                'expectedPaidPrice' => 5001, // 実際には確認しない。メモ程度。
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '15歳 限度額あり(5000円まで) 初回で限度額を超えて購入できない' => [
                'purchasePrice' => '99999.00',
                'errorCode' => ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 99999, // 実際には確認しない。メモ程度。
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '15歳 限度額あり(5000円まで) リセットされて購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20090701,
                'beforeAge' => 15,
                'expectedAge' => 15,
                'beforePaidPrice' => 4999,
                'expectedPaidPrice' => 120,
                'beforeRenotifyAt' => '2024-06-30 15:00:00', // 累計課金額リセット
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],

            '16->17歳 限度額あり(20000円まで) 年齢上昇によって購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20070701,
                'beforeAge' => 16,
                'expectedAge' => 17,
                'beforePaidPrice' => 4999,
                'expectedPaidPrice' => 5119,
                'beforeRenotifyAt' => '2024-07-31 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => '2024-07-31 15:00:00',
            ],
            '17->18歳 限度額なし 年齢上昇によって購入できる' => [
                'purchasePrice' => '120.00',
                'errorCode' => null,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => 20060701,
                'beforeAge' => 17,
                'expectedAge' => 18,
                'beforePaidPrice' => 19999,
                'expectedPaidPrice' => 0, // 累計課金額を加算していく必要がなくなり0に更新される
                'beforeRenotifyAt' => '2024-07-30 15:00:00', // 累計課金額リセット不要
                'expectedRenotifyAt' => null, // 課金限度額なしになるのでリセット日時がない
            ],
            '年齢未設定で購入不可' => [
                'purchasePrice' => '120.00',
                'errorCode' => ErrorCode::USER_BIRTHDATE_NOT_REGISTERED,
                'fixTime' => '2024-07-01 00:00:00',
                'intBirthDate' => null,
                // これ以降の設定は無意味ですが一応設定
                'beforeAge' => 0,
                'expectedAge' => 0,
                'beforePaidPrice' => 0,
                'expectedPaidPrice' => 0, // 累計課金額を加算していく必要がなくなり0に更新される
                'beforeRenotifyAt' => null,
                'expectedRenotifyAt' => null, // 課金限度額なしになるのでリセット日時がない
            ],
        ];
    }

    #[DataProvider('params_test_購入処理で年齢に応じて課金限度額が変わっていることを確認')]
    public function test_購入処理で年齢に応じて課金限度額が変わっていることを確認(
        string $purchasePrice,
        ?int $errorCode,
        string $fixTime,
        ?int $intBirthDate,
        int $beforeAge,
        int $expectedAge,
        int $beforePaidPrice,
        int $expectedPaidPrice,
        ?string $beforeRenotifyAt,
        ?string $expectedRenotifyAt
    ) {
        // Setup
        $now = $this->fixTime($fixTime);
        $usrUserId = $this->createUsrUser()->getId();
        $user = new CurrentUser($usrUserId);

        $this->createDiamond($usrUserId, 0, 0, 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => $beforeAge,
            'paid_price' => $beforePaidPrice,
            'renotify_at' => $beforeRenotifyAt,
        ]);
        $birthDate = is_null($intBirthDate) ? '' : Crypt::encryptString($intBirthDate);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => $birthDate,
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'storeProduct1'; // mst_store_products.id
        $productSubId = 'productSub1'; // opr_products.id

        $rawPriceString = '¥' . $purchasePrice;
        $currencyCode = 'JPY';
        $receipt = $this->makeFakeStoreReceiptString($productId);

        $language = Language::Ja->value;
        $platform = UserConstant::PLATFORM_IOS;

        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::DIAMOND->value,
            'paid_amount' => 100,
        ])->toEntity();
        $mstStoreProduct = MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ])->toEntity();

        UsrStoreAllowance::factory()->create(
            [
                'usr_user_id' => $usrUserId,
                'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
                'billing_platform' => $billingPlatform,
                'product_id' => $productId,
                'mst_store_product_id' => $storeProductId,
                'product_sub_id' => $productSubId,
                'device_id' => $usrUserId . ' device',
            ]
        );

        if (!is_null($errorCode)) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Execute
        $this->shopPurchaseUseCase->exec(
            $user,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $language,
        );

        // Verify
        $usrStoreInfo = UsrStoreInfo::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedAge, $usrStoreInfo->age);
        $this->assertEquals($expectedPaidPrice, $usrStoreInfo->paid_price);
        $this->assertEquals($expectedRenotifyAt, $usrStoreInfo->renotify_at);

        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(100, $diamond->getPaidAmountApple());
        $this->assertEquals(0, $diamond->getPaidAmountGoogle());
    }

    public static function params_test_通貨コードに応じた課金チェック実行確認()
    {
        return [
            'JPY通貨の場合は課金チェックが実行され限度額超過でエラーが発生する' => [
                'currencyCode' => 'JPY',
                'rawPricePrefix' => '¥',
                'expectException' => true,
                'expectedErrorCode' => ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                'expectedPaidPrice' => 19999, // エラーのため更新されない
            ],
            'USD通貨の場合は課金チェックがスキップされ限度額を超えても購入できる' => [
                'currencyCode' => 'USD',
                'rawPricePrefix' => '$',
                'expectException' => false,
                'expectedErrorCode' => null,
                'expectedPaidPrice' => 19999, // USD通貨では課金額制限チェックがスキップされるため更新されない
            ],
        ];
    }

    #[DataProvider('params_test_通貨コードに応じた課金チェック実行確認')]
    public function test_通貨コードに応じた課金チェック実行確認(
        string $currencyCode,
        string $rawPricePrefix,
        bool $expectException,
        ?int $expectedErrorCode,
        int $expectedPaidPrice
    ) {
        // Setup
        $now = $this->fixTime('2024-07-01 00:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $user = new CurrentUser($usrUserId);

        $this->createDiamond($usrUserId, 0, 0, 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // 17歳で限度額20000円のユーザーを作成
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 17,
            'paid_price' => 19999, // 限度額直前
            'renotify_at' => '2024-07-31 15:00:00',
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString(20070701),
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $productId = 'edmo_pack_160_1_framework';
        $storeProductId = 'storeProduct1';
        $productSubId = 'productSub1';
        $purchasePrice = '120.00'; // JPYなら限度額を超える金額
        $rawPriceString = $rawPricePrefix . $purchasePrice;
        $receipt = $this->makeFakeStoreReceiptString($productId);
        $language = Language::Ja->value;
        $platform = UserConstant::PLATFORM_IOS;

        OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::DIAMOND->value,
            'paid_amount' => 100,
        ])->toEntity();

        MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ])->toEntity();

        UsrStoreAllowance::factory()->create([
            'usr_user_id' => $usrUserId,
            'os_platform' => CurrencyConstants::OS_PLATFORM_IOS,
            'billing_platform' => $billingPlatform,
            'product_id' => $productId,
            'mst_store_product_id' => $storeProductId,
            'product_sub_id' => $productSubId,
            'device_id' => $usrUserId . ' device',
        ]);

        if ($expectException) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($expectedErrorCode);
        }

        // Execute
        $result = $this->shopPurchaseUseCase->exec(
            $user,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $language,
        );

        if (!$expectException) {
            // 正常に購入処理が完了していることを確認
            $this->assertNotNull($result);
            $this->assertEquals($productSubId, $result->usrStoreProduct->getProductSubId());

            // 実際の購入が成功していることを確認
            $diamond = $this->getDiamond($usrUserId);
            $this->assertEquals(100, $diamond->getPaidAmountApple());
        }

        // paid_priceの確認（例外の場合でも確認可能）
        $usrStoreInfo = UsrStoreInfo::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedPaidPrice, $usrStoreInfo->paid_price);
    }

    /**
     * allowanceがない状態でも正常に処理され、購入上限を超えた場合にエラーコード11014が発生することを確認
     */
    public function test_allowanceがない状態でも正常に処理され購入上限を超えた場合にエラーコード11014が発生する()
    {
        // Setup
        $now = $this->fixTime('2024-07-01 00:00:00');
        $usrUserId = $this->createUsrUser()->getId();
        $user = new CurrentUser($usrUserId);

        $this->createDiamond($usrUserId, 0, 0, 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // 年齢設定（18歳以上で課金限度額なし）
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
            'paid_price' => 0,
            'renotify_at' => null,
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => Crypt::encryptString(20040701),
        ]);

        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;

        $productId = 'edmo_pack_160_1_framework'; // mst_store_products.product_id_ios
        $storeProductId = 'edmo_pack_160_1_framework'; // mst_store_products.id
        $productSubId = 'edmo_pack_160_1_framework'; // opr_products.id

        $purchasePrice = '120.00';
        $rawPriceString = '¥' . $purchasePrice;
        $currencyCode = 'JPY';
        $receipt = $this->makeFakeStoreReceiptNoSandbox($productId)->getReceipt();

        $language = Language::Ja->value;
        $platform = UserConstant::PLATFORM_IOS;

        // 購入上限を3回に設定
        $purchasableCount = 3;
        $oprProduct = OprProduct::factory()->create([
            'id' => $productSubId,
            'mst_store_product_id' => $storeProductId,
            'product_type' => ProductType::DIAMOND->value,
            'paid_amount' => 100,
            'purchasable_count' => $purchasableCount,
        ])->toEntity();

        $mstStoreProduct = MstStoreProduct::factory()->create([
            'id' => $storeProductId,
            'product_id_ios' => $productId,
            'product_id_android' => "android_{$storeProductId}",
        ])->toEntity();

        // 既に購入上限に達している状態を作成（purchase_count = 3）
        UsrStoreProduct::factory()->create([
            'usr_user_id' => $usrUserId,
            'product_sub_id' => $productSubId,
            'purchase_count' => $purchasableCount, // 既に上限に達している
            'purchase_total_count' => $purchasableCount,
            'last_reset_at' => $now->format('Y-m-d H:i:s'),
        ]);

        // 重要: UsrStoreAllowanceは作成しない（allowanceがない状態をテスト）

        // Expectation
        // BILLING_ALLOWANCE_FAILEDエラーが出ずにBILLING_TRANSACTION_END_PURCHASE_LIMITが出ることを確認
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BILLING_TRANSACTION_END_PURCHASE_LIMIT);

        // Execute
        $this->shopPurchaseUseCase->exec(
            $user,
            $platform,
            $billingPlatform,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $language,
        );
    }
}
