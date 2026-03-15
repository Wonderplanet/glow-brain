<?php

declare(strict_types=1);

namespace WonderPlanet;

use Illuminate\Support\Facades\View;
use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;

/**
 * 管理ツールで使用するマスターデータ・アセットデータインポートのサービスプロバイダー
 *
 * registerで設定ファイルのマージを行なっているため、BaseServiceProviderを継承している
 */
class MasterAssetReleaseAdminServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        // admin用の登録処理
        $publishes = [];
        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_master_asset_release_admin.php'] = config_path('wp_master_asset_release_admin.php');

        // adminのマイグレーションファイルを登録
        $files = glob(__DIR__ . '/../database_admin/migrations/*.php');
        foreach ($files as $file) {
            $publishes[$file] = database_path('migrations/' . basename($file));
        }

        // ライブラリのbladeファイルをプロジェクト側で呼び出せるように設定
        View::addNamespace('view-master-asset-admin', __DIR__ . '/../resources/');

        $this->publishes($publishes, 'wp-admin');
        
        // ライブラリのクラスファイルを公開
        $this->publishClassFiles(__DIR__ . '/../copy_files/app/');
    }
    
    public function register(): void
    {
        parent::register();

        // 設定ファイルのマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_master_asset_release_admin.php',
            'wp_master_asset_release_admin'
        );
    }
}
