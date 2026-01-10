using GLOW.Scenes.ClientUpdate.Presentation.Presenter;
using GLOW.Scenes.ClientUpdate.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ClientUpdate.Application.Installers
{
    public class ClientUpdateDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ClientUpdateDialogViewController>();
            Container.BindInterfacesTo<ClientUpdateDialogPresenter>().AsCached();
        }
    }
}
