using System.Collections.Generic;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Domain.ValueObjects;
using GLOW.Scenes.Shop.Presentation.Presenter;
using GLOW.Scenes.Shop.Presentation.Translator;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using GLOW.Scenes.ShopProductInfo.Presentation.View;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-3_プリズム購入画面（コンティニュー）
    /// </summary>
    public class DiamondPurchasePresenter : IDiamondPurchaseViewDelegate
    {
        [Inject] DiamondPurchaseViewController.Argument Argument { get; }
        [Inject] DiamondPurchaseViewController ViewController { get; }
        [Inject] GetStoreProductListUseCase GetStoreProductListUseCase { get; }
        [Inject] GetShopProductItemUseCase GetShopProductItemUseCase { get; }
        [Inject] ConfirmAdvertisementProductBuyUseCase ConfirmAdvertisementProductBuyUseCase { get; }
        [Inject] ConfirmProductBuyWithCashUseCase ConfirmProductBuyWithCashUseCase { get; }
        [Inject] BuyShopProductUseCase BuyShopProductUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IShopConfirmViewUtil ShopConfirmViewUtil { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] UserStoreInfoUseCase UserStoreInfoUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ShopPurchasePresentationHandler ShopPurchasePresentationHandler { get; }
        [Inject] IInAppPurchaseExecuteDelegate InAppPurchaseExecuteDelegate { get; }

        void IDiamondPurchaseViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(DiamondPurchasePresenter), nameof(IDiamondPurchaseViewDelegate.OnViewDidLoad));

            var storeProductModels = GetStoreProductListUseCase.GetStoreProductList();

            var cellViewModelList = ShopViewModelTranslator
                .ToPaidDiamondShopProductCellViewModels(storeProductModels)
                .ToList();

            ViewController.SetShopViewModel(cellViewModelList);
            ViewController.SetupShopDisplay();
        }

        void IDiamondPurchaseViewDelegate.OnViewDidAppear()
        {
            ApplicationLog.Log(nameof(DiamondPurchasePresenter), nameof(IDiamondPurchaseViewDelegate.OnViewDidAppear));

            ViewController.PlayCellAppearanceAnimation();
        }

        void IDiamondPurchaseViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(DiamondPurchasePresenter), nameof(IDiamondPurchaseViewDelegate.OnViewDidUnload));

            Argument.OnViewClosed?.Invoke();
        }

        void IDiamondPurchaseViewDelegate.OnItemIconSelected(ShopProductCellViewModel shopProductCellViewModel)
        {
            var resourceModel = GetShopProductItemUseCase.GetPlayerResource(
                shopProductCellViewModel.ResourceType,
                shopProductCellViewModel.ResourceId,
                shopProductCellViewModel.ProductResourceAmount);
            if (resourceModel.IsEmpty()) return;

            ItemDetailWireFrame.ShowItemDetailView(resourceModel.Type, resourceModel.Id, resourceModel.Amount, ViewController);
        }

        void IDiamondPurchaseViewDelegate.OnProductInfoSelected(ShopProductCellViewModel shopProductCellViewModel)
        {
            var controller = ViewFactory.Create<ShopProductInfoViewController>();

            var resourceModel = shopProductCellViewModel.PlayerResourceIconViewModel;
            var productName = resourceModel.ResourceType == ResourceType.Item
                ? shopProductCellViewModel.ProductName
                : ProductName.FromTypeAndName(
                    resourceModel.ResourceType,
                    ItemName.Empty,
                    CharacterName.Empty,
                    ProductResourceAmount.Empty);

            controller.SetShopItemViewModelTop(resourceModel, productName);

            ViewController.PresentModally(controller);
        }

        void IDiamondPurchaseViewDelegate.OnPurchaseButtonTapped(
            ShopProductCellViewModel shopProductCellViewModel,
            UIIndexPath indexPath)
        {
            if (shopProductCellViewModel.DisplayShopProductType != DisplayShopProductType.Diamond) return;

            switch (shopProductCellViewModel.DisplayCostType)
            {
                case DisplayCostType.Ad:
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    ConfirmAdvertisementProductBuy(shopProductCellViewModel.ProductId, indexPath);
                    break;
                case DisplayCostType.Cash:
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    ConfirmProductBuyWithCash(shopProductCellViewModel.ProductId, indexPath);
                    break;
            }
        }

        void IDiamondPurchaseViewDelegate.ShowDiamondPurchaseHistory()
        {
            var vc = ViewFactory.Create<DiamondPurchaseHistoryViewController>();
            ViewController.PresentModally(vc);
        }

        void IDiamondPurchaseViewDelegate.OnSpecificCommerceSelected()
        {
            // 特定商取引ダイアログ表示
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IDiamondPurchaseViewDelegate.OnFundsSettlementSelected()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement);
        }

        void IDiamondPurchaseViewDelegate.OnCloseSelected()
        {
            ApplicationLog.Log(nameof(DiamondPurchasePresenter), nameof(IDiamondPurchaseViewDelegate.OnCloseSelected));

            ViewController.Dismiss();
        }

        void ConfirmAdvertisementProductBuy(MasterDataId productId, UIIndexPath indexPath)
        {
            var confirmationModel = ConfirmAdvertisementProductBuyUseCase.ConfirmAdvertisementProductBuy(productId);

            var limitItems = LimitAmountModelCalculator
                .FilteringLimitAmount(
                    confirmationModel.ProductModel.ProductContents.Select(i =>
                        new LimitCheckModel(i.Id, i.Type, i.Amount.Value)).ToList()
                );
            if (1 <= limitItems.Count)
            {
                LimitAmountWireframe.ShowItemPurchaseLimitView();
                return;
            }

            PurchaseShopProduct(productId, indexPath);
        }

        void ConfirmProductBuyWithCash(
            MasterDataId oprProductId,
            UIIndexPath indexPath)
        {
            ShowConfirmView(oprProductId, indexPath);
        }

        void ShowConfirmView(
            MasterDataId oprProductId,
            UIIndexPath indexPath)
        {
            var confirmationModel = ConfirmProductBuyWithCashUseCase.ConfirmProductBuyWithCash(oprProductId);

            var viewModel = ProductBuyWithCashConfirmationViewModelTranslator
                .ToProductBuyWithCashConfirmationViewModel(confirmationModel);

            ShopConfirmViewUtil.ShowDiamondConfirmView(
                ViewController,
                viewModel,
                () =>
                {
                    var limitItems = LimitAmountModelCalculator
                        .FilteringLimitAmount(
                            confirmationModel.ProductModel.ProductContents.Select(i =>
                                    new LimitCheckModel(
                                        i.Id,
                                        i.Type,
                                        i.Amount.Value))
                                .ToList()
                        );
                    if (1 <= limitItems.Count)
                    {
                        // 有償石向け画面
                        LimitAmountWireframe.ShowPaidDiamondPurchaseLimitView();
                        return;
                    }

                    PurchaseStoreProduct(oprProductId, indexPath);
                });
        }

        void PurchaseShopProduct(MasterDataId productId, UIIndexPath indexPath)
        {
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                var commonReceiveResourceModel = await BuyShopProductUseCase.BuyProduct(cancellationToken, productId);
                HomeHeaderDelegate.UpdateStatus();

                var viewModel =
                    CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(commonReceiveResourceModel);

                CommonReceiveWireFrame.Show(new List<CommonReceiveResourceViewModel>() { viewModel });

                UpdatePurchasedProduct(indexPath);
            });
        }

        void PurchaseStoreProduct(MasterDataId productId, UIIndexPath indexPath)
        {
            InAppPurchaseExecuteDelegate.ExecutePurchase(
                ViewController.ActualView.GetCancellationTokenOnDestroy(),
                async ct =>
                {
                    await ShopPurchasePresentationHandler.PurchaseStoreProduct(ct, productId);

                    UpdatePurchasedProduct(indexPath);
                });
        }

        void UpdatePurchasedProduct(UIIndexPath indexPath)
        {
            var storeProductModels = GetStoreProductListUseCase.GetStoreProductList();
            var cellViewModelList = ShopViewModelTranslator
                .ToPaidDiamondShopProductCellViewModels(storeProductModels)
                .ToList();

            ViewController.SetShopViewModel(cellViewModelList);
            ViewController.UpdatePurchasedProductUi(indexPath);
        }
    }
}
