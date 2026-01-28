using GLOW.Core.Domain.Updaters;
using GLOW.Core.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Application.Installers.Views;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using GLOW.Modules.CommonWebView.Application.Installers;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.DiamondPurchaseHistory.Application;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.Shop.Domain.Factories;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Presentation.Presenter;
using GLOW.Scenes.ShopBuyConform.Application.Installers.View;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Facade;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopProductInfo.Application.Installers;
using GLOW.Scenes.ShopProductInfo.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Application.Installers.Views
{
    public class DiamondPurchaseViewControllerInstaller : Installer
    {
        [Inject] DiamondPurchaseViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.BindViewWithKernal<DiamondPurchaseViewController>();
            Container.BindInterfacesTo<DiamondPurchasePresenter>().AsCached();
            Container.Bind<DiamondPurchaseWireframe>().AsCached();

            Container.Bind<GetStoreProductListUseCase>().AsCached();
            Container.Bind<GetShopProductItemUseCase>().AsCached();
            Container.Bind<ConfirmAdvertisementProductBuyUseCase>().AsCached();
            Container.Bind<ConfirmProductBuyWithCashUseCase>().AsCached();
            Container.Bind<CalculateCostEnoughUseCase>().AsCached();
            Container.Bind<BuyShopProductUseCase>().AsCached();
            Container.Bind<BuyStoreProductUseCase>().AsCached();
            Container.Bind<ParentalConsentIfMinorUseCase>().AsCached();
            Container.Bind<ShopPurchasePresentationHandler>().AsCached();
            Container.Bind<CheckShopPurchaseLimitUseCase>().AsCached();

            Container.BindInterfacesTo<ShopPurchaseResultUpdater>().AsCached();
            Container.BindInterfacesTo<LimitAmountWireframe>().AsCached();
            Container.BindInterfacesTo<ItemDetailWireFrame>().AsCached();
            Container.BindInterfacesTo<LimitAmountModelCalculator>().AsCached();
            Container.BindInterfacesTo<ShopProductModelCalculator>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveRewardEvaluator>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveRewardAmountCalculator>().AsCached();

            Container.BindInterfacesTo<ConfirmationShopProductModelFactory>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<ShopProductInfoViewController, ShopProductInfoViewControllerInstaller>();

            Container.BindInterfacesTo<ShopConfirmViewUtil>().AsCached();
            Container.BindInterfacesTo<ShopBuyConfirmViewFacade>().AsCached();
            Container.BindViewFactoryInfo<CoinBuyConfirmViewController, CoinBuyConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<CashBuyConfirmViewController, CashBuyConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondBuyConfirmViewController, DiamondBuyConfirmViewControllerInstaller>();

            Container.Bind<CommonReceiveWireFrame>().AsCached();
            Container.BindViewFactoryInfo<CommonReceiveViewController, CommonReceiveViewControllerInstaller>();
            Container.BindViewFactoryInfo<AsyncCommonReceiveViewController, AsyncCommonReceiveViewControllerInstaller>();

            Container.BindInterfacesTo<CommonWebViewControl>().AsCached();
            Container.BindViewFactoryInfo<CommonWebViewController, CommonWebViewControllerInstaller>();

            Container.BindInterfacesTo<InGameDummyHomeHeaderPresenter>().AsCached().IfNotBound();

            Container.BindViewFactoryInfo<DiamondPurchaseHistoryViewController, DiamondPurchaseHistoryViewInstaller>();

        }
    }
}
