using GLOW.Scenes.AnnouncementWindow.Application.Installers;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using GLOW.Scenes.MaintenanceDialog.Presentation.Presenter;
using GLOW.Scenes.MaintenanceDialog.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.MaintenanceDialog.Application.Installers
{
    public class MaintenanceDialogViewControllerInstaller : Installer
    {
        [Inject] MaintenanceDialogViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<MaintenanceDialogViewController>();
            Container.BindInterfacesTo<MaintenanceDialogPresenter>().AsCached();
        }
    }
}
