<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class LogAllowanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogAllowanceRepository $logAllowanceReposiory;

    public function setUp(): void
    {
        parent::setUp();

        $this->logAllowanceReposiory = $this->app->make(LogAllowanceRepository::class);
    }

    #[Test]
    public function insertAllowanceLog_ログが登録されていること()
    {
        // Exercise
        $id = $this->logAllowanceReposiory->insertAllowanceLog(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'product1',
            'mst_product1',
            'product_sub1',
            'device1',
            new Trigger(
                'trigger_type1',
                'trigger_id1',
                'trigger_name',
                'trigger_detail1'
            )
        );

        // Verify
        $logAllowance = $this->logAllowanceReposiory->findById($id);
        $this->assertEquals('1', $logAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $logAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $logAllowance->billing_platform);
        $this->assertEquals('product1', $logAllowance->product_id);
        $this->assertEquals('mst_product1', $logAllowance->mst_store_product_id);
        $this->assertEquals('product_sub1', $logAllowance->product_sub_id);
        $this->assertEquals('device1', $logAllowance->device_id);
        $this->assertEquals('trigger_type1', $logAllowance->trigger_type);
        $this->assertEquals('trigger_id1', $logAllowance->trigger_id);
        $this->assertEquals('trigger_name', $logAllowance->trigger_name);
        $this->assertEquals('trigger_detail1', $logAllowance->trigger_detail);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestIdType()->value, $logAllowance->request_id_type);
        $this->assertEquals(CurrencyCommon::getRequestUniqueIdData()->getRequestId(), $logAllowance->request_id);
    }
}
