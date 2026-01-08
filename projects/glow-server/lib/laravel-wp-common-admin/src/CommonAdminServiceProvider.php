<?php

declare(strict_types=1);

namespace WonderPlanet;

use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;

/**
 * 管理ツール共通機能用サービスプロバイダー
 *
 * registerで設定ファイルのマージを行なっているため、BaseServiceProviderを継承している
 */
class CommonAdminServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        // admin用の登録処理
        $publishes = [];
        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_common_admin.php'] = config_path('wp_common_admin.php');

        $this->publishes($publishes, 'wp-admin');
    }
    
    public function register(): void
    {
        parent::register();

        // 設定ファイルのマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_common_admin.php',
            'wp_common_admin'
        );
    }
}
