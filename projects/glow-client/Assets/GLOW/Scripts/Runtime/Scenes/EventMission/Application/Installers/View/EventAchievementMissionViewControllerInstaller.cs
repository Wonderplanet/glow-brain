using GLOW.Scenes.EventMission.Presentation.Presenter;
using GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EventMission.Application.Installers.View
{
    public class EventAchievementMissionViewControllerInstaller : Installer
    {
        [Inject] EventAchievementMissionViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<EventAchievementMissionViewController>();
            Container.BindInterfacesTo<EventAchievementMissionPresenter>().AsCached();
        }
    }
}