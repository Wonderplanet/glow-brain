using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Presenters;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.LinkBnIdWebViewDialog.Application.Views
{
    public class LinkBnIdWebViewDialogViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<LinkBnIdWebViewDialogViewController>();
            Container.BindInterfacesTo<LinkBnIdWebViewDialogPresenter>().AsCached();
        }
    }
}
