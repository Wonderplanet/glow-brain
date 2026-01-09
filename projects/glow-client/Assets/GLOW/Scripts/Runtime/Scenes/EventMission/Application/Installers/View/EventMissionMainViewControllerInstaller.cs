using GLOW.Core.Data.Repositories;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.EventMission.Presentation.Presenter;
using GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.Mission.Domain.Creator;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Application.Installers.View
{
    public class EventMissionMainViewControllerInstaller : Installer
    {
        [Inject] EventMissionMainViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<EventMissionMainViewController>();
            Container.BindInterfacesTo<EventMissionMainPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<EventAchievementMissionViewController, EventAchievementMissionViewControllerInstaller>();
            Container.BindViewFactoryInfo<EventDailyBonusViewController, EventDailyBonusViewControllerInstaller>();

            Container.BindInterfacesTo<MissionEventCacheRepository>().AsCached();

            Container.Bind<FetchEventMissionUseCase>().AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
            Container.Bind<ReceiveEventMissionRewardUseCase>().AsCached();
            Container.Bind<BulkReceiveEventMissionRewardUseCase>().AsCached();
            Container.Bind<GetEventMissionTimeInformationUseCase>().AsCached();
            Container.Bind<UpdatedReceivingEventDailyBonusUseCase>().AsCached();
            Container.Bind<FetchEventMissionCommonHeaderUseCase>().AsCached();

            Container.BindInterfacesTo<MissionResultModelFactory>().AsCached();
            Container.BindInterfacesTo<MissionEventCacheModelFactory>().AsCached();
        }
    }
}
