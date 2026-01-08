using GLOW.Scenes.LinkBnIdDialog.Domain;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Presenters;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.LinkBnIdWebViewDialog.Application.Views;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.LinkBnIdDialog.Application.Views
{
    public class LinkBnIdDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<LinkBnIdDialogViewController>();
            Container.BindInterfacesTo<LinkBnIdDialogPresenter>().AsCached();
            Container.Bind<LinkBnIdUseCase>().AsCached();
            
            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<LinkBnIdWebViewDialogViewController, LinkBnIdWebViewDialogViewControllerInstaller>();
        }
    }
}
