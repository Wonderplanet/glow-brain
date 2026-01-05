<?php

declare(strict_types=1);

namespace WonderPlanet;

use WonderPlanet\Domain\Cache\Delegators\APCuCacheDelegator;
use WonderPlanet\Domain\Cache\Delegators\RedisCacheDelegator;
use WonderPlanet\Domain\Cache\Fasades\APCuCache;
use WonderPlanet\Domain\Cache\Fasades\RedisCache;
use WonderPlanet\Domain\Common\Delegators\WpCommonDelegator;
use WonderPlanet\Domain\Common\Facades\WpCommon;
use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;

/**
 * 共通ライブラリのサービスプロバイダ
 *
 * registerで設定ファイルのマージを行なっているため、BaseServiceProviderを継承している
 */
class CommonServiceProvider extends BaseServiceProvider
{
    protected array $classes = [
        APCuCacheDelegator::class,
        RedisCacheDelegator::class,
        WpCommonDelegator::class,
    ];

    protected array $facades = [
        APCuCache::FACADE_ACCESSOR => APCuCacheDelegator::class,
        RedisCache::FACADE_ACCESSOR => RedisCacheDelegator::class,
        WpCommon::FACADE_ACCESSOR => WpCommonDelegator::class,
    ];

    /**
     * @return void
     */
    public function boot()
    {
        $publishes = [];

        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_common.php'] = config_path('wp_common.php');

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
        $publishes[__DIR__ . '/../config/wp_common.php'] = config_path('wp_common.php');

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

        // 設定ファイルのマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_common.php',
            'wp_common'
        );
    }
}
