using GLOW.Scenes.AnnouncementWindow.Presentation.Presenter;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Application.Installers
{
    public class AnnouncementContentViewControllerInstaller : Installer
    {
        [Inject] AnnouncementContentViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<AnnouncementContentViewController>();
            Container.BindInterfacesTo<AnnouncementContentPresenter>().AsCached();
        }
    }
}