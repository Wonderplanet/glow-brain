<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class UsrStoreAllowanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UsrStoreAllowanceRepository $usrStoreAllowanceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrStoreAllowanceRepository = $this->app->make(UsrStoreAllowanceRepository::class);
    }

    #[Test]
    public function insertStoreAllowance_allowanceが登録されていること()
    {
        // Exercise
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);
    }

    #[Test]
    public function deleteStoreAllowance_allowanceが削除されていること()
    {
        // Setup
        $storeAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );

        // Exercise
        $this->usrStoreAllowanceRepository->deleteStoreAllowance('1', $storeAllowance->id);

        // Verify
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');
        $this->assertNull($usrStoreAllowance);
    }

    #[Test]
    public function findById_指定したIDのallowanceが取得できること()
    {
        // Setup
        $storeAllowance = $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );

        // Exercise
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findById($storeAllowance->id);

        // Verify
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);
    }

    #[Test]
    public function findByUserIdAndProductId_購入許可情報をストアプロダクトIDから取得()
    {
        // Setup
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );

        // Exercise
        $usrStoreAllowance = $this->usrStoreAllowanceRepository->findByUserIdAndProductId('1', CurrencyConstants::PLATFORM_APPSTORE, 'store_product1');

        // Verify
        $this->assertEquals('1', $usrStoreAllowance->usr_user_id);
        $this->assertEquals(CurrencyConstants::OS_PLATFORM_IOS, $usrStoreAllowance->os_platform);
        $this->assertEquals(CurrencyConstants::PLATFORM_APPSTORE, $usrStoreAllowance->billing_platform);
        $this->assertEquals('store_product1', $usrStoreAllowance->product_id);
        $this->assertEquals('mst_product1', $usrStoreAllowance->mst_store_product_id);
        $this->assertEquals('product1', $usrStoreAllowance->product_sub_id);
        $this->assertEquals('device1', $usrStoreAllowance->device_id);
    }

    #[Test]
    public function forceDeleteByUserId_完全削除する()
    {
        // Setup
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );
        // 別のユーザー情報
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '2',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );

        // Exercise
        $this->usrStoreAllowanceRepository->forceDeleteByUserId('1');

        // Verify
        $usrStoreAllowance = UsrStoreAllowance::query()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertNull($usrStoreAllowance);

        // 別のユーザー情報は削除されていないこと
        $usrStoreAllowance = UsrStoreAllowance::query()
            ->where('usr_user_id', '2')
            ->first();
        $this->assertEquals('2', $usrStoreAllowance->usr_user_id);
    }

    #[Test]
    public function findAllByUserId_ユーザーの許可情報を全て取得する()
    {
        // Setup
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product1',
            'mst_product1',
            'product1',
            'device1',
        );
        $this->usrStoreAllowanceRepository->insertStoreAllowance(
            '1',
            CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            'store_product2',
            'mst_product2',
            'product2',
            'device2',
        );

        // Exercise
        $usrStoreAllowances = $this->usrStoreAllowanceRepository->findAllByUserId('1');

        // Verify
        $this->assertCount(2, $usrStoreAllowances);
        // store_product1のデータがあること
        $allowance = array_values(array_filter($usrStoreAllowances, fn ($allowance) => $allowance->product_id === 'store_product1'))[0];
        $this->assertEquals('1', $allowance->usr_user_id);
        // store_product2のデータがあること
        $allowance = array_values(array_filter($usrStoreAllowances, fn ($allowance) => $allowance->product_id === 'store_product2'))[0];
        $this->assertEquals('1', $allowance->usr_user_id);
    }
}
