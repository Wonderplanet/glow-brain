using GLOW.Scenes.GameModeSelect.Domain;
using GLOW.Scenes.GameModeSelect.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeMainBanner;
using GLOW.Scenes.QuestContentTop.Domain;
using GLOW.Scenes.QuestSelect.Domain;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    public sealed class HomeMainViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMainViewController>();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();
            Container.Bind<HomeMainBadgeUseCase>().AsCached();
            Container.Bind<HomeMainBannerUseCase>().AsCached();
            Container.BindInterfacesTo<HomeMainPresenter>().AsCached();

            Container.BindInterfacesTo<GameModeSelectPresenter>().AsCached();
            Container.Bind<GameModeSelectUseCase>().AsCached().IfNotBound();
            Container.Bind<SelectQuestUseCase>().AsCached().IfNotBound();
            Container.Bind<UpdateCurrentMstQuestIdUseCase>().AsCached().IfNotBound();

            // HomeAppearanceAction
            Container.BindInterfacesTo<HomeAppearanceActionExecutor>().AsCached();
            Container.BindFactory<AdventBattleRankingResultAction, AdventBattleRankingResultAction.Factory>().AsCached();
            Container.BindFactory<AnnouncementAction, AnnouncementAction.Factory>().AsCached();
            Container.BindFactory<DailyBonusAction, DailyBonusAction.Factory>().AsCached();
            Container.BindFactory<DeferredPurchaseResultAction, DeferredPurchaseResultAction.Factory>().AsCached();
            Container.BindFactory<EventDailyBonusAction, EventDailyBonusAction.Factory>().AsCached();
            Container.BindFactory<HeaderExpGaugeAnimationAction, HeaderExpGaugeAnimationAction.Factory>().AsCached();
            Container.BindFactory<InGameNoticeAction, InGameNoticeAction.Factory>().AsCached();
            Container.BindFactory<QuestInitializationAction, QuestInitializationAction.Factory>().AsCached();
            Container.BindFactory<TutorialAction, TutorialAction.Factory>().AsCached();
            Container.BindFactory<ComebackDailyBonusAction, ComebackDailyBonusAction.Factory>().AsCached();

            Container.Bind<HomeMissionWireFrame>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<HomeMainBannerItemViewController, HomeMainBannerItemViewInstaller>();
        }
    }
}
