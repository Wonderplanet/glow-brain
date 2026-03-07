<?php

namespace Tests\Unit\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Currency\Services\CurrencyUserService;
use App\Domain\User\Models\UsrUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Models\LogCurrencyFree;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyUserServiceTest extends TestCase
{
    private CurrencyUserService $currencyUserService;
    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyUserService = app(CurrencyUserService::class);
        $this->currencyService = app(CurrencyService::class);
    }

    #[Test]
    public function addIngameFreeDiamond_無償ダイヤを登録()
    {
        // Setup
        $userId = 'test-user-id';
        $platform = PlatformConstant::PLATFORM_IOS;
        // 基盤情報の登録
        $this->currencyService->createUser($userId, CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 0);
        LogCurrencyFree::query()->delete();

        // Exercise
        $this->currencyUserService->addIngameFreeDiamond(
            $userId,
            $platform,
            100,
            new Trigger('test', 'mission1', 'ミッション1', '')
        );

        // Verify
        $summary = $this->currencyService->getCurrencySummary($userId);
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
        $this->currencyService->createUser($userId, $osPlatform, CurrencyConstants::PLATFORM_APPSTORE, 10);

        // Exercise
        $freeDiamond = $this->currencyUserService->getFreeDiamond($userId);

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
        int $paidDiamondWebstore,
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
        $this->currencyService->addCurrencyPaid(
            $usrUser->getId(),
            CurrencyConstants::OS_PLATFORM_WEBSTORE,
            CurrencyConstants::PLATFORM_WEBSTORE,
            $paidDiamondWebstore,
            'JPY',
            '100',
            100,
            'test-webstore',
            true,
            new Trigger('test', '', '', ''),
        );

        if ($isExceptionThrown) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);
        }

        // Exercise
        $result = $this->currencyUserService->validateDiamond($usrUser->getId(), $diamondCost, $platform, $billingPlatform);

        // Verify
        $this->assertNull($result);
    }

    public static function params_validateDiamond_ダイヤ不足の場合にLACK_OF_RESOURCESエラーになる()
    {
        return [
            // LACK_OF_RESOURCESエラーになる

            'ios 無償ダイヤが1足りずエラー' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 99, 0, 0, 0, true],
            'ios 有償ダイヤが1足りずエラー' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 0, 99, 0, 0, true],
            'ios 無償と有償ダイヤの合算が1足りずエラー' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 1, 98, 0, 0, true],

            'android 無償ダイヤが1足りずエラー' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 99, 0, 0, 0, true],
            'android 有償ダイヤが1足りずエラー' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 0, 99, 0, true],
            'android 無償と有償ダイヤの合算が1足りずエラー' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 1, 0, 98, 0, true],

            // エラーなく処理が通る

            'ios 無償ダイヤが足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 101, 0, 0, 0, false],
            'ios 有償ダイヤ(iOSのみ)が足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 0, 101, 0, 0, false],
            'ios 有償ダイヤ(iOSとWebStore)が足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 0, 51, 0, 50, false],
            'ios 有償ダイヤ(WebStoreのみ)が足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 0, 0, 0, 100, false],
            'ios 無償と有償ダイヤの合算が足りている' => [PlatformConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100, 50, 50, 0, 0, false],

            'android 無償ダイヤが足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 101, 0, 0, 0, false],
            'android 有償ダイヤ(Androidのみ)が足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 0, 101, 0, false],
            'android 有償ダイヤ(AndroidとWebStore)が足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 0, 51, 50, false],
            'android 有償ダイヤ(WebStoreのみが足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 0, 0, 0, 100, false],
            'android 無償と有償ダイヤの合算が足りている' => [PlatformConstant::PLATFORM_ANDROID, CurrencyConstants::PLATFORM_GOOGLEPLAY, 100, 50, 0, 50, 0, false],
        ];
    }

    #[Test]
    public function consumeDiamond_ダイヤ消費()
    {
        // Setup
        $userId = 'test-user-id';
        $platform = PlatformConstant::PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        // 基盤情報の登録
        $this->currencyService->createUser($userId, CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 500);
        LogCurrencyFree::query()->delete();
        // 有償ダイヤの登録
        $this->currencyService->addCurrencyPaid(
            $userId,
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            100,
            'JPY',
            '100',
            100,
            'test-apple',
            true,
            new Trigger('test', '', '', '')
        );

        // Exercise
        $this->currencyUserService->consumeDiamond(
            $userId,
            100,
            $platform,
            $billingPlatform,
            new Trigger('test', 'mission1', 'ミッション1', '')
        );

        // Verify
        $summary = $this->currencyService->getCurrencySummary($userId);
        $this->assertEquals(400, $summary->getFreeAmount());
        $this->assertEquals(500, $summary->getPlatformTotalAmount($billingPlatform));
    }
}
