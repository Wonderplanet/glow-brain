using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.PurchaseLimitDialog.Presentation;

namespace GLOW.Scenes.PurchaseLimitDialog.Application
{
    public class PurchaseLimitDialogViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PurchaseLimitDialogViewController>();
            Container.BindInterfacesTo<PurchaseLimitDialogPresenter>().AsCached();
        }
    }
}
