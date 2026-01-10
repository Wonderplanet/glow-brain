using GLOW.Core.Constants.PlatformStore;
using GLOW.Core.Application.Installers.Subs;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.DataStores.Mission;
using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Repositories.Banner;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Modules.BnIdLinker;
using GLOW.Core.Domain.Modules;
using GLOW.Core.Domain.Tracker;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Sorter;
using GLOW.Core.Domain.Updaters;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.Tutorial.Domain.AssetDownloader;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Constants;
using GLOW.Scenes.Login.Domain.UseCase;
using GLOW.Scenes.PassShop.Domain.Updater;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.StaminaRecover.Domain.Factory;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.TitleMenu.Domain;
using WonderPlanet.ObservabilityKit;
using WPFramework.Modules.Log;
using WPFramework.Modules.Platform;
using Zenject;
using IBannerLoadSupport = GLOW.Core.Presentation.Modules.IBannerLoadSupport;

namespace GLOW.Core.Application.Installers
{
    internal sealed class ApplicationInstaller : MonoInstaller<ApplicationInstaller>
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(ApplicationInstaller), nameof(MonoInstaller.InstallBindings));

            // NOTE: フレームワークのインストール
            // NOTE: システム全体で利用するリポジトリ
            // Container.Install<FrameworkInstaller>();
            Container.BindInterfacesTo<AnalyticsTracker>().AsCached();

            Container.BindInterfacesTo<GameInteractionFactory>().AsCached();

            // TODO: ApplicationWireFrameみたいな動きになる予定
            Container.BindInterfacesTo<ApplicationCenter>().AsCached();
            Container.BindInterfacesTo<ContentMaintenanceCoordinator>().AsCached();

            // NOTE: バージョンアップ時などの際にストア遷移を受け持つ処理をインストール
            Container.Bind<PlatformStoreLinker>()
                .FromInstance(new PlatformStoreLinker(PlatformStoreId.AppStore, PlatformStoreId.GooglePlay)).AsCached();

            Container.Bind<BnIdLinker>().AsCached();
            Container.Bind<UserDataDeleteUseCase>().AsCached();

            Container.Install<AudioInstaller>();
            Container.Install<GlowUIInstaller>();
            Container.Install<AssetBundleInstaller>();
            Container.Install<ScreenInteractionControlInstaller>();
            Container.Install<TimeInstaller>();
            Container.Install<GlowNetworkInstaller>();
            Container.Install<GlowErrorHandleInstaller>();
            Container.Install<LoginInstaller>();
            Container.Install<MstDataInstaller>();
            Container.Install<GameCommonInstaller>();
            Container.Install<UserDataInstaller>();
            Container.Install<GameApiInstaller>();
            // Container.Install<LegalNoticeInstaller>();
            Container.Install<LocalizationLocaleSelectInstaller>();
            Container.Install<BenchmarkInstaller>();
            Container.Install<InAppAdvertisingInstaller>();
            Container.Install<EnvironmentInstaller>();
            // Container.Install<InAppPurchaseInstaller>();

            Container.Install<ModelFactoryInstaller>();
            Container.BindInterfacesTo<GetApplicationInfoInteractor>().AsCached();
            Container.Bind<IsUserDataCreatedUseCase>().AsCached();
            Container.BindInterfacesTo<HapticsPresenter>().AsCached();

            Container.BindInterfacesTo<GlowBannerManager>().AsCached();

            Container.BindInterfacesTo<SpecialAttackCutInLogRepository>().AsCached();
            Container.BindInterfacesTo<SpecialAttackCutInLogLocalDataStore>().AsCached();

            //NOTE: super-light > seed-prodの差し替え時の追加

            Container.BindInterfacesTo<StageLimitStatusModelFactory>().AsCached();
            Container.BindInterfacesTo<SelectedStageEvaluator>().AsCached();
            Container.BindInterfacesTo<StageOrderEvaluator>().AsCached();
            Container.BindInterfacesTo<SelectedStageRepository>().AsCached();
            Container.BindInterfacesTo<ServerTimeProvider>().AsCached();
            Container.BindInterfacesTo<PreferenceRepository>().AsCached();
            Container.BindInterfacesTo<PartyCacheRepository>().AsCached();
            Container.BindInterfacesTo<HeldPassEffectRepository>().AsCached();
            Container.BindInterfacesTo<HeldPassEffectRepositoryUpdater>().AsCached();
            Container.BindInterfacesTo<AnnouncementCacheRepository>().AsCached();
            Container.BindInterfacesTo<MissionCacheRepository>().AsCached();
            Container.BindInterfacesTo<CampaignModelFactory>().AsCached();
            Container.BindInterfacesTo<AcquisitionDisplayedUnitIdsRepository>().AsCached();

            Container.BindInterfacesTo<PvpTopCacheRepository>().AsCached();
            Container.BindInterfacesTo<PvpSelectedOpponentStatusCacheRepository>().AsCached();
            Container.BindInterfacesTo<MstCurrentPvpModelResolver>().AsCached();
            Container.BindInterfacesTo<PvpReceivedRewardRepository>().AsCached();

            Container.BindInterfacesTo<ReceivedEventDailyBonusRepository>().AsCached();
            Container.BindInterfacesTo<ReceivedDailyBonusRepository>().AsCached();
            Container.BindInterfacesTo<ReceivedComebackDailyBonusRepository>().AsCached();
            Container.BindInterfacesTo<ReceivedEventDailyBonusDataStore>().AsCached();
            Container.BindInterfacesTo<ReceivedDailyBonusDataStore>().AsCached();
            Container.BindInterfacesTo<ReceivedComebackDailyBonusDataStore>().AsCached();

            // SpriteLoadSupportのGLOW拡張に置き換え
            Container.Rebind<IBannerLoadSupport>().To<BannerLoadSupportEx>().AsCached();

            //チュートリアルダウンロード用
            Container.BindInterfacesTo<TutorialAssetDownloader>().AsCached();

            Container.BindInterfacesTo<LocalNotifier>().AsCached();
            Container.BindInterfacesTo<LocalNotificationScheduler>().AsCached();
            Container.BindInterfacesTo<GachaEvaluator>().AsCached();
            Container.BindInterfacesTo<UserStaminaModelFactory>().AsCached();

            Container.BindInterfacesTo<PlayerResourceSorter>().AsCached();

            Container.BindInterfacesTo<UserLevelUpCacheRepository>().AsCached();

            // 部分メンテ系
            Container.Bind<ContentMaintenanceWireframe>().AsCached();
            Container.Bind<CheckContentMaintenanceUseCase>().AsCached();
            Container.Bind<GetContentMaintenanceTypeUseCase>().AsCached();
            Container.Bind<SessionCleanupUseCase>().AsCached();

            InstallNavigator();
            InstallHomeBackGround();
            InstallDailyRefresh();
            InstallInAppPurchase();
        }

        void InstallNavigator()
        {
            Container.BindInterfacesTo<ResumableStateRepository>().AsCached();
        }

        void InstallHomeBackGround()
        {
            Container.Bind<HomeCurrentQuestSelectFactory>().AsCached();
        }

        void InstallDailyRefresh()
        {
            Container.BindInterfacesTo<DailyResetTimeCalculator>().AsCached();
            Container.Bind<DailyRefreshCheckUseCase>().AsCached();
            Container.Bind<DailyRefreshWireFrame>().AsCached();
        }

        void InstallInAppPurchase()
        {
            Container.BindInterfacesTo<StoreCoreModule>().AsCached();
            Container.BindInterfacesTo<InAppPurchasePresenter>().AsCached();
            Container.BindInterfacesTo<ShopPurchaseResultUpdater>().AsCached();
        }
    }
}
