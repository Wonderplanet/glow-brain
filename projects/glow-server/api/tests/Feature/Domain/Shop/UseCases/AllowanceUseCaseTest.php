<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\UseCases\AllowanceUseCase;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\Unit\BaseUseCaseTestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class AllowanceUseCaseTest extends BaseUseCaseTestCase
{
    private AllowanceUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AllowanceUseCase::class);
    }

    public static function params_test_購入許可情報を登録する(): array
    {
        return [
            'allowanceレコードがないときはinsertして登録できる' => [false],
            'allowanceレコードがあるときはdeleteしてからinsertして登録できる' => [true],
        ];
    }

    #[DataProvider('params_test_購入許可情報を登録する')]
    public function test_購入許可情報を登録する(bool $isExistUsrStoreAllowance)
    {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $currentUser = new CurrentUser($userId);
        $now = $this->fixTime('2025-02-01 00:00:00');

        $deviceId = $userId . ' device';
        $storeProductId = 'ios_edmo_pack_160_1_framework';
        $mstProductId = 'edmo_pack_160_1_framework';
        $productSubId = 'edmo_pack_160_1_framework';
        $expectedProductId = 'ios_edmo_pack_160_1_framework';
        $platform = System::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $language = Language::Ja->value;
        $currencyCode = 'JPY';
        $price = "100.000";

        // mst
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();

        $this->createDiamond($userId);

        // usr
        UsrUserProfile::factory()->create([
            'usr_user_id' => $userId,
            'birth_date' => '2000-01-01',
        ]);
        UsrStoreInfo::factory()->create(
            [
                'usr_user_id' => $userId,
                'age' => 25,
            ]
        );
        if ($isExistUsrStoreAllowance) {
            UsrStoreAllowance::factory()->create(
                [
                    'usr_user_id' => $userId,
                    'os_platform' => $osPlatform,
                    'billing_platform' => $billingPlatform,
                    'product_id' => $storeProductId,
                    'mst_store_product_id' => $mstProductId,
                    'product_sub_id' => $productSubId,
                    'device_id' => $deviceId,
                ]
            );
        }

        // Exercise
        $result = $this->useCase->__invoke(
            $currentUser,
            $platform,
            $billingPlatform,
            $storeProductId,
            $productSubId,
            $language,
            $currencyCode,
            $price,
        );

        // Verify
        $this->assertEquals($productSubId, $result['product_sub_id']);
        $this->assertEquals($expectedProductId, $result['product_id']);
    }

    public static function params_test_購入許可情報登録のエラー(): array
    {
        return [
            '商品マスタがない' => [
                function () {},
            ],
            '商品マスタが期限切れ' => [
                function ($oprProductId) {
                    MstStoreProduct::factory()->create([
                        'id' => $oprProductId,
                        'product_id_ios' => "ios_{$oprProductId}",
                        'product_id_android' => "android_{$oprProductId}",
                    ]);
                    OprProduct::factory()->create(
                        [
                            'id' => $oprProductId,
                            'mst_store_product_id' => $oprProductId,
                            'product_type' => ProductType::PACK->value,
                            'paid_amount' => 100,
                            'start_date' => '2025-01-01 00:00:00',
                            'end_date' => '2025-01-31 23:59:59',
                        ]
                    );
                },
            ],
        ];
    }

    #[DataProvider('params_test_購入許可情報登録のエラー')]
    public function test_購入許可情報登録のエラー(callable $prepareProductMaster)
    {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $currentUser = new CurrentUser($userId);
        $now = $this->fixTime('2025-02-01 00:00:00');

        $deviceId = $userId . ' device';
        $storeProductId = 'ios_edmo_pack_160_1_framework';
        $mstProductId = 'edmo_pack_160_1_framework';
        $productSubId = 'edmo_pack_160_1_framework';
        $expectedProductId = 'ios_edmo_pack_160_1_framework';
        $platform = System::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $language = Language::Ja->value;
        $currencyCode = 'JPY';
        $price = "100.000";

        // mst
        $prepareProductMaster($mstProductId);

        $this->createDiamond($userId);

        // usr
        UsrUserProfile::factory()->create([
            'usr_user_id' => $userId,
            'birth_date' => '2000-01-01',
        ]);
        UsrStoreInfo::factory()->create(
            [
                'usr_user_id' => $userId,
                'age' => 25,
            ]
        );

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        $result = $this->useCase->__invoke(
            $currentUser,
            $platform,
            $billingPlatform,
            $storeProductId,
            $productSubId,
            $language,
            $currencyCode,
            $price,
        );
    }

    public function test_課金額制限に引っかかってBILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDEDが投げられることを確認()
    {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $currentUser = new CurrentUser($userId);
        $now = $this->fixTime('2025-02-01 00:00:00');

        $deviceId = $userId . ' device';
        $storeProductId = 'ios_edmo_pack_160_1_framework';
        $mstProductId = 'edmo_pack_160_1_framework';
        $productSubId = 'edmo_pack_160_1_framework';
        $platform = System::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $language = Language::Ja->value;
        $currencyCode = 'JPY';
        $price = "120.00";

        // mst
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();

        $this->createDiamond($userId);

        // 17歳で課金限度額20000円に設定し、既に限度額を超えている状態を作成
        $intBirthDate = 20080201; // 17歳
        $birthDate = Crypt::encryptString((string)$intBirthDate);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $userId,
            'birth_date' => $birthDate,
        ]);

        // 限度額を1円超えた状態
        $beforePaidPrice = 20000 - 120 + 1;
        UsrStoreInfo::factory()->create([
            'usr_user_id' => $userId,
            'age' => 17,
            'paid_price' => $beforePaidPrice,
            'renotify_at' => '2025-03-01 00:00:00',
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED);

        $this->useCase->__invoke(
            $currentUser,
            $platform,
            $billingPlatform,
            $storeProductId,
            $productSubId,
            $language,
            $currencyCode,
            $price,
        );
    }
}
