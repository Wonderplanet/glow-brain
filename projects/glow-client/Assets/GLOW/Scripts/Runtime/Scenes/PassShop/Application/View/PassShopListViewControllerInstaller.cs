using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Presenter;
using GLOW.Scenes.PassShop.Presentation.View;
using GLOW.Scenes.ShopTab.Domain.Factory;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PassShop.Application.View
{
    public class PassShopListViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PassShopListViewController>();
            Container.BindInterfacesTo<PassShopListPresenter>().AsCached();

            Container.BindInterfacesTo<ShowPassShopProductFactory>().AsCached();
            Container.Bind<ShowPassShopProductListUseCase>().AsCached();
            Container.Bind<CheckPassPurchasableUseCase>().AsCached();
            Container.BindInterfacesTo<PassExceptionMessageWireframe>().AsCached();
        }
    }
}
