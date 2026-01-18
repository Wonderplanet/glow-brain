<?php

declare(strict_types=1);

namespace WonderPlanet;

use WonderPlanet\Domain\Billing\Delegators\BillingAdminDelegator;
use WonderPlanet\Domain\Billing\Delegators\BillingBatchDelegator;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Delegators\BillingInternalDelegator;
use WonderPlanet\Domain\Billing\Facades\Billing;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\CertificateManager;
use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyBatchDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyCommonDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDebugDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalAdminDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalDelegator;
use WonderPlanet\Domain\Currency\Facades\Currency;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;

class CurrencyServiceProvider extends BaseServiceProvider
{
    /**
     * scoped/遅延プロバイダとして登録するクラスのリスト
     *
     * BillingとCurrencyのDelegatorを登録する
     *
     * @var array<string>
     */
    protected array $classes = [
        // billing
        BillingAdminDelegator::class,
        BillingBatchDelegator::class,
        BillingDelegator::class,
        BillingInternalDelegator::class,

        // currency
        CurrencyAdminDelegator::class,
        CurrencyBatchDelegator::class,
        CurrencyDebugDelegator::class,
        CurrencyDelegator::class,
        CurrencyInternalAdminDelegator::class,
        CurrencyInternalDelegator::class,
        CurrencyCommonDelegator::class,
    ];

    /**
     * Facade/遅延プロバイダとして登録するクラスのリスト
     *
     * BillingとCurrencyのFacadeを登録する
     *
     * @var array<string, string>
     */
    protected array $facades = [
        // billing
        Billing::FACADE_ACCESSOR => BillingDelegator::class,

        // currency
        Currency::FACADE_ACCESSOR => CurrencyDelegator::class,
        CurrencyCommon::FACADE_ACCESSOR => CurrencyCommonDelegator::class,
    ];

    /**
     * @return void
     */
    public function boot()
    {
        $publishes = [];

        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_currency.php'] = config_path('wp_currency.php');

        // マイグレーションファイルの登録
        // migrationsディレクトリすべてのファイルをコピーする
        $files = glob(__DIR__ . '/../database/migrations/*.php');
        foreach ($files as $file) {
            $publishes[$file] = database_path('migrations/' . basename($file));
        }

        $this->publishes($publishes, 'wp');

        // admin用の登録処理
        $publishes = []; // 一旦初期化
        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_currency.php'] = config_path('wp_currency.php');

        // adminのマイグレーションファイルを登録
        $files = glob(__DIR__ . '/../database_admin/migrations/*.php');
        foreach ($files as $file) {
            $publishes[$file] = database_path('migrations/' . basename($file));
        }
        $this->publishes($publishes, 'wp-admin');
    }

    public function register()
    {
        parent::register();

        // CertificateManagerをsingleton登録
        $this->app->singleton(CertificateManager::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_currency.php',
            'wp_currency'
        );
    }
}
