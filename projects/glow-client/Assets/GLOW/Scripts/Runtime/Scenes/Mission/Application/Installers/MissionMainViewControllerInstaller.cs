using GLOW.Core.Data.Services;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.Mission.Application.Installers.View;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.AchievementMission;
using GLOW.Scenes.Mission.Presentation.View.DailyBonusMission;
using GLOW.Scenes.Mission.Presentation.View.DailyMission;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Mission.Presentation.View.WeeklyMission;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Application.Installers
{
    public class MissionMainViewControllerInstaller : Installer
    {
        [Inject] MissionMainViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<MissionMainViewController>();
            Container.BindInterfacesTo<MissionMainPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<AchievementMissionViewController, AchievementMissionViewControllerInstaller>();
            Container.BindViewFactoryInfo<DailyBonusMissionViewController, DailyBonusMissionViewControllerInstaller>();
            Container.BindViewFactoryInfo<DailyMissionViewController, DailyMissionViewControllerInstaller>();
            Container.BindViewFactoryInfo<WeeklyMissionViewController, WeeklyMissionViewControllerInstaller>();

            Container.Bind<MissionScreenInteractionControl>().AsCached();
            Container.Bind<FetchMissionListUseCase>().AsCached();
            Container.Bind<GetMissionNextUpdateTimeUseCase>().AsCached();
            Container.Bind<ReceiveMissionRewardUseCase>().AsCached();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
            Container.Bind<BulkReceiveMissionRewardUseCase>().AsCached();
            Container.Bind<ShowBonusPointMissionRewardReceivingUseCase>().AsCached();

            Container.BindInterfacesTo<MissionResultModelFactory>().AsCached();
        }
    }
}
