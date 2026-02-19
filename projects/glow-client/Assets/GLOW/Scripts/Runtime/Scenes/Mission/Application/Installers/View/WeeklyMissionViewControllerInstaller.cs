using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.WeeklyMission;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Mission.Application.Installers.View
{
    public class WeeklyMissionViewControllerInstaller: Installer
    {
        [Inject] WeeklyMissionViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(WeeklyMissionViewControllerInstaller), nameof(InstallBindings));

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<WeeklyMissionViewController>();
            Container.BindInterfacesTo<WeeklyMissionPresenter>().AsCached();
        }
    }
}
