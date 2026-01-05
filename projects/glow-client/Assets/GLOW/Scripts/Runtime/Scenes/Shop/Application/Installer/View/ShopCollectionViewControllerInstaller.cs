using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.Factories;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Presentation.Presenter;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopProductInfo.Application.Installers;
using GLOW.Scenes.ShopProductInfo.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.Shop.Application.Installer.View
{
    public class ShopCollectionViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<ShopCollectionViewController>();
            Container.BindInterfacesTo<ShopCollectionPresenter>().AsCached();

            Container.Bind<GetShopProductListUseCase>().AsCached();
            Container.Bind<GetStoreProductListUseCase>().AsCached();
            Container.Bind<ConfirmAdvertisementProductBuyUseCase>().AsCached();
            Container.Bind<ConfirmProductBuyWithCoinUseCase>().AsCached();
            Container.Bind<ConfirmProductBuyWithDiamondUseCase>().AsCached();
            Container.Bind<ConfirmProductBuyWithCashUseCase>().AsCached();
            Container.Bind<BuyShopProductUseCase>().AsCached();
            Container.Bind<BuyStoreProductUseCase>().AsCached();
            Container.Bind<GetShopNextUpdateTimeUseCase>().AsCached();
            Container.Bind<GetShopProductItemUseCase>().AsCached();
            Container.Bind<GetProductLimitedTimeUseCase>().AsCached();
            Container.Bind<CurrentPlayerResourceInfoUseCase>().AsCached();
            Container.Bind<ShopPurchasePresentationHandler>().AsCached();
            
            Container.BindInterfacesTo<ConfirmationShopProductModelFactory>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<ShopProductInfoViewController, ShopProductInfoViewControllerInstaller>();
        }
    }
}
