<?php

namespace Tests\Feature\Domain\Currency\Delegators;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\User\Models\UsrUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class AppCurrencyDelegatorTest extends TestCase
{
    private AppCurrencyDelegator $appCurrencyDelegator;
    private CurrencyDelegator $currencyDelegator;
    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appCurrencyDelegator = app(AppCurrencyDelegator::class);
        $this->currencyDelegator = app(CurrencyDelegator::class);
        $this->currencyService = app(CurrencyService::class);
    }

    #[Test]
    public function addIngameFreeDiamond_無償ダイヤを登録()
    {
        // Setup
        $userId = 'test-user-id';
        $platform = PlatformConstant::PLATFORM_IOS;
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        // 基盤情報の登録
        $this->currencyDelegator->createUser($userId, $osPlatform, CurrencyConstants::PLATFORM_APPSTORE, 0);
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->appCurrencyDelegator->addIngameFreeDiamond(
            $userId,
            $platform,
            100,
            new Trigger('test', 'mission1', 'ミッション1', '')
        );

        // Verify
        $summary = $this->currencyDelegator->getCurrencySummary($userId);
        $this->assertEquals(100, $summary->getFreeAmount());

        // 無償一次通貨の確認
        $freeCurrency = $this->currencyService->getCurrencyFree($userId);
        $this->assertEquals(100, $freeCurrency->getIngameAmount());

        // ログの確認
        $logCurrencyFree = LogCurrencyFree::query()->where('usr_user_id', $userId)->get()->first();
        $this->assertEquals(100, $logCurrencyFree->change_ingame_amount);
        $this->assertEquals('test', $logCurrencyFree->trigger_type);
        $this->assertEquals('mission1', $logCurrencyFree->trigger_id);
        $this->assertEquals('ミッション1', $logCurrencyFree->trigger_name);
        $this->assertEquals('', $logCurrencyFree->trigger_detail);
    }

    #[Test]
    public function getFreeDiamond_無償ダイヤの数を取得()
    {
        // Setup
        $userId = 'test-user-id';
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        // 基盤情報の登録
        $this->currencyDelegator->createUser($userId, $osPlatform, CurrencyConstants::PLATFORM_APPSTORE, 10);

        // Exercise
        $freeDiamond = $this->appCurrencyDelegator->getFreeDiamond($userId);

        // Verify
        $this->assertEquals(10, $freeDiamond);
    }

    #[Test]
    #[DataProvider('params_validateDiamond_ダイヤ不足の場合にLACK_OF_RESOURCESエラーになる')]
    public function validateDiamond_ダイヤ不足の場合にLACK_OF_RESOURCESエラーになる(
        int $platform,
        string $billingPlatform,
        int $diamondCost,
        int $freeDiamond,
        int $paidDiamondIos,
        int $paidDiamondAndroid,
        bool $isExceptionThrown,
    ) {
        // Setup
        $usrUser = UsrUser::factory()->create();

        // 課金基盤
        $this->currencyService->createUser(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $freeDiamond,
        );

        // ダイヤが1足りない状態にする
        $this->currencyService->addCurrencyPaid(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            $paidDiamondIos,
            'JPY',
            '100',
            100,
            'test-apple',
            true,
            new Trigger('test', '', '', ''),
        );
        $this->currencyService->addCurrencyPaid(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_ANDROID,
            CurrencyConstants::PLATFORM_GOOGLEPLAY,
            $paidDiamondAndroid,
            'JPY',
            '100',
            100,
            'test-apple',
            true,
            new Trigger('test', '', '', ''),
        );

        if ($isExceptionThrown) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);
        }

        // Exercise
        $result = $this->appCurrencyDelegator->validateDiamond($usrUser->getId(), $diamondCost, $platform, $billingPlatform);

        // Verify
        $this->assertNull($result);
    }

    public static function params_validateDiamond_ダイヤ不足の場合にLACK_OF_RESOURCESエラーになる()
    {
        return [
            // エラーなく処理が通る

            'ios 無償ダイヤが足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 101, 0, 0, false],
            'ios 有償ダイヤが足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 0, 101, 0, false],
            'ios 無償と有償ダイヤの合算が足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 50, 50, 0, false],

            'android 無償ダイヤが足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 101, 0, 0, false],
            'android 有償ダイヤが足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 0, 101, false],
            'android 無償と有償ダイヤの合算が足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 50, 0, 50, false],
        ];
    }

    public function getOsPlatformByIntegerPlatform_OSプラットフォームの取得()
    {
        $params = [
            1 => CurrencyConstants::OS_PLATFORM_IOS,
            2 => CurrencyConstants::OS_PLATFORM_ANDROID
        ];

        foreach ($params as $platform => $expected) {
            $actual = $this->appCurrencyDelegator->getOsPlatformByIntegerPlatform($platform);
            $this->assertEquals($expected, $actual);
        }
    }

    public static function getPlatform() {
        return [
            "iOS" => [CurrencyConstants::PLATFORM_APPSTORE, CurrencyConstants::OS_PLATFORM_IOS, 'ios_edmo_pack_160_1_framework'],
            "Android" => [CurrencyConstants::PLATFORM_GOOGLEPLAY, CurrencyConstants::OS_PLATFORM_ANDROID, 'android_edmo_pack_160_1_framework'],
        ];
    }

    #[Test]
    #[DataProvider('getPlatform')]
    public function getOprProductByProductId_ProductIdでOprProductを取得できる(
        $billingPlatform,
        $osPlatform,
        $productId
    ) {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $this->currencyService->registerCurrencySummary($userId, $osPlatform, 0);
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();
        // Exercise
        $oprProduct = $this->appCurrencyDelegator->getOprProductByProductId(
            $productId,
            $billingPlatform,
            \Carbon\CarbonImmutable::now(),
        );
        // Verify
        $this->assertEquals('edmo_pack_160_1_framework', $oprProduct->getId());
        $this->assertEquals('edmo_pack_160_1_framework', $oprProduct->getMstStoreProductId());
        $this->assertEquals(100, $oprProduct->getPaidAmount());
        $this->assertEquals(ProductType::DIAMOND->value, $oprProduct->getProductType());
    }

    #[Test]
    #[DataProvider('getPlatform')]
    public function getOprProductByProductId_ProductIdと違う日時指定ではOprProductを取得できない(
        $billingPlatform,
        $osPlatform,
        $productId
    ) {
        // Setup
        $user = $this->createUsrUser();
        $userId = $user->getId();
        $this->currencyService->registerCurrencySummary($userId, $osPlatform, 0);
        MstStoreProduct::factory()->createMockData();
        OprProduct::factory()->createMockData();
        // Exercise
        $oprProduct = $this->appCurrencyDelegator->getOprProductByProductId(
            $productId,
            $billingPlatform,
            \Carbon\CarbonImmutable::parse('1989-12-31 23:59:59'),
        );
        // Verify
        $this->assertEquals(true, empty($oprProduct));
    }
}
