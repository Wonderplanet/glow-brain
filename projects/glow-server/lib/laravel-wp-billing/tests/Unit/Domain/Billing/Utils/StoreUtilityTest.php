<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Utils;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class StoreUtilityTest extends TestCase
{
    use RefreshDatabase;

    private string $bkProductionBundleId = '';
    private string $bkSandboxBundleId = '';
    private string $bkPackageName = '';

    public function setUp(): void
    {
        parent::setUp();

        // テスト中にproduction_bundle_id、sandbox_bundle_id、 package_nameを更新するためバックアップを取得
        $this->bkProductionBundleId = Config::get('wp_currency.store.app_store.production_bundle_id', '');
        $this->bkSandboxBundleId = Config::get('wp_currency.store.app_store.sandbox_bundle_id', '');
        $this->bkPackageName = Config::get('wp_currency.store.googleplay_store.package_name', '');
    }

    public function tearDown(): void
    {
        // production_bundle_id、sandbox_bundle_id、 package_nameを元にもどす
        // [getBundleIdOrPackageName_生成チェック]でしか更新してないが、途中でテストがこけてしまった時を考慮して
        // tearDownで戻している
        Config::set('wp_currency.store.app_store.production_bundle_id', $this->bkProductionBundleId);
        Config::set('wp_currency.store.app_store.sandbox_bundle_id', $this->bkSandboxBundleId);
        Config::set('wp_currency.store.googleplay_store.package_name', $this->bkPackageName);

        parent::tearDown();
    }

    #[Test]
    public function getProductionBundleId_生成チェック(): void
    {
        // Setup
        //  テスト用にenvの値を更新(tearDownで元に戻している)
        Config::set('wp_currency.store.app_store.production_bundle_id', 'prd_bundle');

        // Exercise
        $result = StoreUtility::getProductionBundleId();

        // Verify
        $this->assertEquals('prd_bundle', $result);
    }

    #[Test]
    public function getSandboxBundleId_生成チェック(): void
    {
        // Setup
        //  テスト用にenvの値を更新(tearDownで元に戻している)
        Config::set('wp_currency.store.app_store.sandbox_bundle_id', 'sandbox_bundle');

        // Exercise
        $result = StoreUtility::getSandboxBundleId();

        // Verify
        $this->assertEquals('sandbox_bundle', $result);
    }

    #[Test]
    public function getPackageName_生成チェック(): void
    {
        // Setup
        //  テスト用にenvの値を更新(tearDownで元に戻している)
        Config::set('wp_currency.store.googleplay_store.package_name', 'gg_pkg_name');

        // Exercise
        $result = StoreUtility::getPackageName();

        // Verify
        $this->assertEquals('gg_pkg_name', $result);
    }

    #[Test]
    #[DataProvider('getBundleIdOrPackageNameData')]
    public function getBundleIdOrPackageName_生成チェック(
        bool $isSandbox,
        string $billingPlatform,
        string $expected
    ): void {
        // Setup
        //  テスト用にenvの値を更新(tearDownで元に戻している)
        Config::set('wp_currency.store.app_store.production_bundle_id', 'prd_bundle');
        Config::set('wp_currency.store.app_store.sandbox_bundle_id', 'sandbox_bundle');
        Config::set('wp_currency.store.googleplay_store.package_name', 'gg_pkg_name');

        // Exercise
        $result = StoreUtility::getBundleIdOrPackageName($isSandbox, $billingPlatform);

        // Verify
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public static function getBundleIdOrPackageNameData(): array
    {
        return [
            // $isSandbox, $billingPlatform, $expected
            '本番用bundleId取得' => [false, CurrencyConstants::PLATFORM_APPSTORE, 'prd_bundle'],
            'sandbox用bundleId取得' => [true, CurrencyConstants::PLATFORM_APPSTORE, 'sandbox_bundle'],
            'packageName取得' => [false, CurrencyConstants::PLATFORM_GOOGLEPLAY, 'gg_pkg_name'],
        ];
    }
}
