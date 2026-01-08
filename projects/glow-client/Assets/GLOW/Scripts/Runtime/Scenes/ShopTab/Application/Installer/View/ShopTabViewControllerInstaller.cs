using GLOW.Core.Data.Repositories;
using GLOW.Core.Presentation.Presenters;
using GLOW.Scenes.PackShop.Application.Views;
using GLOW.Scenes.PackShop.Presentation.Views;
using GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent;
using GLOW.Scenes.PackShopGacha.Application;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using GLOW.Scenes.PackShopProductInfo.Application.Views;
using GLOW.Scenes.PackShopProductInfo.Presentation.Views;
using GLOW.Scenes.PassShop.Application.View;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.View;
using GLOW.Scenes.PassShopBuyConfirm.Application.View;
using GLOW.Scenes.PassShopBuyConfirm.Domain.Factory;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.View;
using GLOW.Scenes.PassShopProductDetail.Application.View;
using GLOW.Scenes.PassShopProductDetail.Domain.Factory;
using GLOW.Scenes.PassShopProductDetail.Presentation.View;
using GLOW.Scenes.Shop.Application.Installer.View;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Presentation.Presenter;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Application.Installers.View;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Facade;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopTab.Domain.Factory;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.ShopTab.Presentation.Presenter;
using GLOW.Scenes.ShopTab.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopTab.Application.Installer.View
{
    public class ShopTabViewControllerInstaller : Zenject.Installer
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(ShopTabViewControllerInstaller), "InstallBindings");
            Container.BindInterfacesTo<ViewFactory>().AsCached();

            Container.BindViewWithKernal<ShopTabViewController>();
            Container.BindInterfacesTo<ShopTabViewPresenter>().AsCached();

            Container.Bind<SaveNewShopProductUseCase>().AsCached();
            Container.Bind<InitializeNewShopProductIdUseCase>().AsCached();
            Container.Bind<ParentalConsentIfMinorUseCase>().AsCached();

            InstallCommon();
            // InstallGacha();
            InstallShop();
            InstallPack();
            InstallPass();
        }

        void InstallCommon()
        {
            Container.Bind<GetShopProductNoticeUseCase>().AsCached();
            Container.Bind<CheckShopPurchaseLimitUseCase>().AsCached();

            Container.BindInterfacesTo<ShopConfirmViewUtil>().AsCached();
            Container.Bind<LimitAmountModelCalculator>().AsCached();
            Container.BindInterfacesTo<LimitAmountWireframe>().AsCached();
            Container.BindInterfacesTo<ProductNameFactory>().AsCached();
            Container.BindInterfacesTo<PassReceivableRewardModelFactory>().AsCached();
        }

        void InstallShop()
        {
            Container.BindViewFactoryInfo<ShopCollectionViewController, ShopCollectionViewControllerInstaller>();
            Container.BindInterfacesTo<ShopCacheRepository>().AsCached();
            Container.BindInterfacesTo<ShopBuyConfirmViewFacade>().AsCached();
            Container.BindViewFactoryInfo<CoinBuyConfirmViewController, CoinBuyConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<CashBuyConfirmViewController, CashBuyConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondBuyConfirmViewController, DiamondBuyConfirmViewControllerInstaller>();
            Container.Bind<CalculateCostEnoughUseCase>().AsCached();
        }

        void InstallPack()
        {
            Container.BindViewFactoryInfo<PackShopViewController, PackShopViewControllerInstaller>();
            Container.BindViewFactoryInfo<StageClearPackPageContentViewController, StageClearPackPageContentViewControllerInstaller>();
            Container.BindViewFactoryInfo<PackShopProductInfoViewController, PackShopInfoViewControllerInstaller>();
            Container.BindViewFactoryInfo<PackShopGachaViewController, PackShopGachaViewControllerInstaller>();
            Container.Bind<GetPackProductNoticeUseCase>().AsCached();
        }

        void InstallPass()
        {
            Container.BindViewFactoryInfo<PassShopListViewController, PassShopListViewControllerInstaller>();
            Container.BindViewFactoryInfo<PassShopProductDetailViewController, PassShopProductDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<PassShopBuyConfirmViewController, PassShopBuyConfirmViewControllerInstaller>();
            Container.Bind<PurchasePassUseCase>().AsCached();
            Container.BindInterfacesTo<ShowPassShopProductFactory>().AsCached();
            Container.Bind<GetPassProductNoticeUseCase>().AsCached();
        }
    }
}
