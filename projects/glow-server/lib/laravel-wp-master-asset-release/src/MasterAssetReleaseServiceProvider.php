<?php

declare(strict_types=1);

namespace WonderPlanet;

use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\AssetReleaseDelegator;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\MasterReleaseDelegator;
use WonderPlanet\Domain\MasterAssetRelease\Facades\AssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Facades\MasterReleaseVersion;

/**
 * apiで使用するマスターデータ・アセットデータインポートのサービスプロバイダー
 *
 * registerで設定ファイルのマージを行なっているため、BaseServiceProviderを継承している
 */
class MasterAssetReleaseServiceProvider extends BaseServiceProvider
{
    protected array $classes = [
        MasterReleaseDelegator::class,
        AssetReleaseDelegator::class,
    ];

    protected array $facades = [
        MasterReleaseVersion::FACADE_ACCESSOR => MasterReleaseDelegator::class,
        AssetReleaseVersion::FACADE_ACCESSOR => AssetReleaseDelegator::class,
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        $publishes = [];

        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_master_asset_release.php'] = config_path('wp_master_asset_release.php');

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
        $publishes[__DIR__ . '/../config/wp_master_asset_release.php'] = config_path('wp_master_asset_release.php');

        // adminのマイグレーションファイルを登録
        $files = glob(__DIR__ . '/../database_admin/migrations/*.php');
        foreach ($files as $file) {
            $publishes[$file] = database_path('migrations/' . basename($file));
        }

        $this->publishes($publishes, 'wp-admin');
    }

    public function register(): void
    {
        parent::register();

        // 設定ファイルのマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_master_asset_release.php',
            'wp_master_asset_release'
        );
    }
}
