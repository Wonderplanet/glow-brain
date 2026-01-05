<?php

namespace App\Providers\Filament;

use App\Constants\NavigationGroups;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use App\Filament\Pages\Auth\Login;
use App\Livewire\QuickUserSearch;
use App\Services\Filament\AdminPanelService;
use App\Services\MasterData\OprMasterReleaseControlAccessService;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeZone;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $isHttps = env('APP_ENV') !== 'local';
        return $panel
            ->bootUsing(function () {
                // 配信中のリリースバージョンDBに接続
                /** @var AdminPanelService $adminPanelService */
                $adminPanelService = $this->app->make(AdminPanelService::class);
                $adminPanelService->setMstDatabaseConnection();
            })
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->darkMode(false)
            ->globalSearch(false)
            ->sidebarFullyCollapsibleOnDesktop()
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
            ])
            ->favicon(asset('image/favicon.png', $isHttps))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                QuickUserSearch::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\AdminInitializeMstDatabaseConnection::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroups::ADMIN->value,
                NavigationGroups::NOTICE->value,
                NavigationGroups::QA_SUPPORT->value,
                NavigationGroups::USER->value,
                NavigationGroups::CS->value,
                NavigationGroups::MASTER_DATA_VIEWER->value,
                NavigationGroups::AGGREGATION->value,
                NavigationGroups::DEBUG->value,
                NavigationGroups::OTHER->value,
            ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole() || $this->app->runningUnitTests()) {
            return;
        }
        $this->headerCustom();
        $this->sideBarSearchCustom();
        $this->tableCustom();
    }

    private function headerCustom(): void
    {
        /** @var MngMasterReleaseService $mngMasterReleaseService */
        $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);

        /** @var MngMasterRelease|null $mngMasterRelease */
        $mngMasterRelease = $mngMasterReleaseService->getLatestReleasedMngMasterRelease();

        $releaseKey = is_null($mngMasterRelease) ? "取得できません" : $mngMasterRelease->toEntity()->getReleaseKey();

        $currentDateTime = new CarbonImmutable();
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $debugSetting = $debugUserAllTimeSettingRepository->get();
        if (isset($debugSetting)) {
            $now = CarbonImmutable::instance(new DateTime('now', new DateTimeZone(config('app.timezone'))));
            $currentDateTime = $debugSetting->getUserAllTime($now);
        }

        $env = config('app.env');
        Filament::serving(function () use ($releaseKey, $env, $currentDateTime){
            FilamentView::registerRenderHook(PanelsRenderHook::TOPBAR_START, function () use ($releaseKey, $env, $currentDateTime) {
                return view('components.custom-topbar', [
                    'version' => $releaseKey,
                    'env' => $env,
                    'currentDateTime' => $currentDateTime,
                    'currentMstDatabase' => config('database.connections.mst.database', '取得できません'),
                ]);
            });
        });

    }

    private function sideBarSearchCustom()
    {
        Filament::serving(function () {
            FilamentView::registerRenderHook(PanelsRenderHook::SIDEBAR_NAV_START, function () {
                return view('components.navigation-search');
            });
        });
    }

    private function tableCustom()
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->paginationPageOptions([5, 10, 25, 50]);
        });

    }
}
