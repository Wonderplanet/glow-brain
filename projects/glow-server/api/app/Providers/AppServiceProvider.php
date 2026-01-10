<?php

namespace App\Providers;

use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Services\DeferredTaskService;
use App\Domain\Common\Utils\LogUtil;
use App\Domain\Common\Utils\PlatformUtil;
use App\Domain\Currency\Utils\CurrencyUtility;
use App\Domain\Mission\MissionManager;
use App\Domain\Reward\Managers\RewardManager;
use App\Infrastructure\LogModelManager;
use App\Infrastructure\MasterRepository;
use App\Infrastructure\UsrModelManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * MasterRepository
         *
         * マスタデータバージョンは1APIリクエスト内で切り替わることはないため、
         * リクエストスコープでインスタンスを保持する
         */
        $this->app->scoped(
            MasterRepository::class,
            function () {
                return new MasterRepository();
            }
        );

        // マスタ・アセット
        $this->app->bind(
            \WonderPlanet\Domain\MasterAssetRelease\Repositories\MngMasterReleaseVersionRepository::class,
            \App\Domain\Resource\Mng\Repositories\MngMasterReleaseVersionRepository::class
        );
        $this->app->bind(
            \WonderPlanet\Domain\MasterAssetRelease\Repositories\MngAssetReleaseVersionRepository::class,
            \App\Domain\Resource\Mng\Repositories\MngAssetReleaseVersionRepository::class
        );

        // UsrModelManager
        $this->app->scoped(UsrModelManager::class, function () {

            $usrUserId = '';

            $user = auth()->user();
            if ($user instanceof CurrentUser) {
                $usrUserId = $user->id;
            }

            $usrModelManager = new UsrModelManager();
            $usrModelManager->setUsrUserId($usrUserId);

            return $usrModelManager;

        });

        // LogModelManager
        $this->app->scoped(LogModelManager::class, function () {
            return new LogModelManager(
                LogUtil::getNginxRequestId(),
                LogUtil::getRequestId(),
            );
        });

        // RewardManager
        $this->app->scoped(RewardManager::class, function () {
            return new RewardManager();
        });
        $this->app->bind(
            \App\Domain\Reward\Managers\RewardManagerInterface::class,
            RewardManager::class
        );

        // MissionManager
        $this->app->scoped(MissionManager::class, function () {
            return new MissionManager();
        });

        // CacheClientManager
        $this->app->scoped(CacheClientManager::class, function () {
            return new CacheClientManager();
        });

        // DeferredTaskService
        $this->app->scoped(DeferredTaskService::class, function () {
            return new DeferredTaskService();
        });

        $this->app->singleton(\Illuminate\Database\Migrations\Migrator::class, function ($app) {
            return $app['migrator'];
        });

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->addCommonHeader();
        if (config('app.debug') && !app()->isProduction()) {
            $router->pushMiddlewareToGroup('api', \App\Http\Middleware\InitializeDebug::class);
        }
    }

    /**
     * リクエストに共通のヘッダ情報を追加する
     *
     * @return void
     */
    private function addCommonHeader()
    {
        // TODO: macroで実装するとIDEの補完がきかないので、FormRequestを継承したクラスに実装するよう変更する
        //   このメソッドは課金基盤向けのplatformを取得している。プロダクト側の文字列とは異なるため注意すること
        Request::macro('getPlatform', function () {
            // headerにplatformがない場合はrequest bodyから取得 (互換性維持のため)
            // TODO: クライアント側で全てのリクエストヘッダにplatformが乗るようになったらそちらだけにする

            // コード上はCall to an undefined method になるためphpstanを無視する
            // @phpstan-ignore-next-line
            $platform = (int)$this->header(
                System::HEADER_PLATFORM,
                $this->input(
                    'header.platform',
                    $this->input('platform', '')
                )
            );

            // プラットフォーム向けの文字列から課金基盤向けの文字列に変換する
            return PlatformUtil::convertPlatformToCurrencyPlatform($platform);
        });

        // 複数箇所で使用するのと、取得する判定が複雑なのでmacroで定義
        Request::macro('getBillingPlatform', function () {
            // header.billing_platformがない場合はplatformからbilling_platformを取得する
            // TODO:
            //   互換性のためplatformから判定をしているが、billing_platformが送信されてくるようになったらパラメータだけを見る
            return $this->input(
                'billing_platform',
                CurrencyUtility::getBillingPlatform($this->getPlatform())
            );
        });
    }
}
