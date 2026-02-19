<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Currency\Delegators;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyPaidRepository;

class CurrencyInternalDelegatorTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyDelegator $currencyDelegator;
    private CurrencyInternalDelegator $currencyInternalDelegator;
    private UsrCurrencyPaidRepository $usrCurrencyPaidRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencyDelegator = $this->app->make(CurrencyDelegator::class);
        $this->currencyInternalDelegator = $this->app->make(CurrencyInternalDelegator::class);
        $this->usrCurrencyPaidRepository = $this->app->make(UsrCurrencyPaidRepository::class);
    }

    #[Test]
    public function addCurrencyPaid_有償一次通貨を追加する()
    {
        // Setup
        //  通貨管理の準備
        $this->currencyDelegator->createUser('1', CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, 100);

        // Exercise
        $this->currencyInternalDelegator->addCurrencyPaid(
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

        // Verify
        // 渡したパラメータから計算した結果が登録されていること
        $usrCurrencyPaid = $this->usrCurrencyPaidRepository->findByUserId('1')[0];
        $this->assertEquals('1', $usrCurrencyPaid->usr_user_id);
    }
}
