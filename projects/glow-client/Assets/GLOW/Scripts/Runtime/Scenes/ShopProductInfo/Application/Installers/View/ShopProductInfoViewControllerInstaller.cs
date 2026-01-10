using GLOW.Scenes.ShopProductInfo.Presentation.Presenter;
using GLOW.Scenes.ShopProductInfo.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;

namespace GLOW.Scenes.ShopProductInfo.Application.Installers
{
    public class ShopProductInfoViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(ShopProductInfoViewControllerInstaller), "InstallBindings");

            Container.BindViewWithKernal<ShopProductInfoViewController>();
            Container.BindInterfacesTo<ShopProductInfoViewPresenter>().AsCached();
        }
    }
}
