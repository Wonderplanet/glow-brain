using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.DailyMission;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Mission.Application.Installers.View
{
    public class DailyMissionViewControllerInstaller: Installer
    {
        [Inject] DailyMissionViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(DailyMissionViewControllerInstaller), nameof(InstallBindings));

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<DailyMissionViewController>();
            Container.BindInterfacesTo<DailyMissionPresenter>().AsCached();
        }
    }
}
