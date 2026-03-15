<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Constants\Errorcode;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Exceptions\WpCurrencyInvalidDebugException;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;
use WonderPlanet\Domain\Currency\Services\CurrencyDebugService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class CurrencyDebugServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;
    private CurrencyDebugService $currencyDebugService;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;
    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;

    private $beforeIsDebuggableEnvironment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyDebugService = $this->app->make(CurrencyDebugService::class);
        $this->currencyService = $this->app->make(CurrencyService::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);

        // テスト用にis_debuggable_environmentを上書きする場合があるので、戻せるように値を保存しておく
        $this->beforeIsDebuggableEnvironment = Config::get('wp_currency.is_debuggable_env');
    }

    protected function tearDown(): void
    {
        // テスト用にis_debuggable_environmentを上書きする場合があるので、開始前の状態に戻す
        Config::set('wp_currency.is_debuggable_env', $this->beforeIsDebuggableEnvironment);

        parent::tearDown();
    }

    #[Test]
    public function addCurrencyPaid_有償一次通貨を追加する()
    {
        // Setup
        // 通貨管理の登録
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        $this->currencyService->registerCurrencySummary('1', CurrencyConstants::OS_PLATFORM_IOS, 0);

        // Exercise
        $id = $this->currencyDebugService->addCurrencyPaid(
            '1',
            $osPlatform,
            $billingPlatform,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', 'unit test id1', 'unit test name', 'detail')
        );

        // Verify
        // 渡したパラメータから計算した結果が登録されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals($id, $usrCurrencyPaid->id);
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
        $this->assertEquals(1, $usrCurrencyPaid->seq_no);
        $this->assertEquals(100, $usrCurrencyPaid->left_amount);
        $this->assertEquals('0.010000', $usrCurrencyPaid->purchase_price);
        $this->assertEquals(100, $usrCurrencyPaid->purchase_amount);
        $this->assertEquals('0.00010000', $usrCurrencyPaid->price_per_amount);
        $this->assertEquals(101, $usrCurrencyPaid->vip_point);
        $this->assertEquals('USD', $usrCurrencyPaid->currency_code);
        $this->assertEquals('dummy receipt 1', $usrCurrencyPaid->receipt_unique_id);
        $this->assertEquals(true, $usrCurrencyPaid->is_sandbox);
        $this->assertEquals($billingPlatform, $usrCurrencyPaid->billing_platform);
        $this->assertEquals($osPlatform, $usrCurrencyPaid->os_platform);

        // サマリーが更新されていること
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencySummary->usr_user_id);
        $this->assertEquals(100, $usrCurrencySummary->paid_amount_apple);
        $this->assertEquals(0, $usrCurrencySummary->paid_amount_google);
    }

    #[Test]
    public function addCurrencyPaid_開発環境ではない場合にエラーになる()
    {
        // Setup
        $osPlatform = CurrencyConstants::OS_PLATFORM_IOS;
        $billingPlatform = CurrencyConstants::PLATFORM_APPSTORE;
        // 開発環境ではないように設定
        Config::set('wp_currency.is_debuggable_env', false);

        // Exercise
        // 開発環境ではないので例外が発生する
        $this->expectException(WpCurrencyInvalidDebugException::class);
        $this->expectExceptionCode(Errorcode::INVALID_DEBUG_ENVIRONMENT);

        $this->currencyDebugService->addCurrencyPaid(
            '1',
            $osPlatform,
            $billingPlatform,
            100,
            'USD',
            '0.01',
            101,
            'dummy receipt 1',
            true,
            new Trigger('unit_test', 'unit test id1', 'unit test name', 'detail')
        );
    }
}
