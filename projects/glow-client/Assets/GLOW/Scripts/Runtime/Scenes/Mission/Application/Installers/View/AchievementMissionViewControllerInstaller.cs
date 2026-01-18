using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.AchievementMission;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.Mission.Application.Installers.View
{
    public class AchievementMissionViewControllerInstaller : Installer
    {
        [Inject] AchievementMissionViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AchievementMissionViewController>();
            Container.BindInterfacesTo<AchievementMissionPresenter>().AsCached();
        }
    }
}
