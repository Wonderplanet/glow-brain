using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Domain.ValueObjects;
using GLOW.Scenes.Shop.Presentation.Translator;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using GLOW.Scenes.ShopProductInfo.Presentation.View;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.ShopTab.Presentation.View;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Shop.Presentation.Presenter
{
    public class ShopCollectionPresenter : IShopCollectionViewDelegate
    {
        [Inject] ShopCollectionViewController ViewController { get; }
        [Inject] GetShopProductListUseCase GetShopProductListUseCase { get; }
        [Inject] GetStoreProductListUseCase GetStoreProductListUseCase { get; }
        [Inject] IShopConfirmViewUtil ShopConfirmViewUtil { get; }
        [Inject] ConfirmAdvertisementProductBuyUseCase ConfirmAdvertisementProductBuyUseCase { get; }
        [Inject] ConfirmProductBuyWithCoinUseCase ConfirmProductBuyWithCoinUseCase { get; }
        [Inject] ConfirmProductBuyWithDiamondUseCase ConfirmProductBuyWithDiamondUseCase { get; }
        [Inject] ConfirmProductBuyWithCashUseCase ConfirmProductBuyWithCashUseCase { get; }
        [Inject] BuyShopProductUseCase BuyShopProductUseCase { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] GetShopNextUpdateTimeUseCase GetShopNextUpdateTimeUseCase { get; }
        [Inject] GetShopProductItemUseCase GetShopProductItemUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IShopTabViewDelegate ShopTabViewDelegate { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] UserStoreInfoUseCase UserStoreInfoUseCase { get; }
        [Inject] ShopPurchasePresentationHandler ShopPurchasePresentationHandler { get; }
        [Inject] IInAppPurchaseExecuteDelegate InAppPurchaseExecuteDelegate { get; }
        [Inject] DailyRefreshWireFrame DailyRefreshWireFrame { get; }
        [Inject] DailyRefreshCheckUseCase DailyRefreshCheckUseCase { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] SaveNewShopProductUseCase SaveNewShopProductUseCase { get; }

        ShopViewModel _shopViewModel;
        ShopPresentationExecSupport _shopPresentationExecSupport;
        CancellationToken ShopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IShopCollectionViewDelegate.ViewWillAppear()
        {
            _shopPresentationExecSupport = new ShopPresentationExecSupport(BuyShopProductUseCase);

            // ViewModelを用意し、Viewを作る
            SetupProductList();

            // CollectionViewの初期化後にアニメーションを実行するためにずらす
            DoAsync.Invoke(ShopCancellationToken, async cancellationToken =>
            {
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
                ViewController.PlayCellAppearanceAnimation();
            });
        }

        void IShopCollectionViewDelegate.ViewDidDisappear()
        {
            // 表示商品IDを保存
            SaveNewShopProductUseCase.SaveShopNewProductContain();
        }

        void IShopCollectionViewDelegate.ShowShopProductInfo(ShopProductCellViewModel model)
        {
            var controller = ViewFactory.Create<ShopProductInfoViewController>();
            var resourceModel = model.PlayerResourceIconViewModel;
            var productName = resourceModel.ResourceType == ResourceType.Item
                ? model.ProductName
                : ProductName.FromTypeAndName(
                    resourceModel.ResourceType,
                    ItemName.Empty,
                    CharacterName.Empty,
                    ProductResourceAmount.Empty);
            controller.SetShopItemViewModelTop(resourceModel, productName);
            ViewController.PresentModally(controller);
        }

        void IShopCollectionViewDelegate.OnItemIconTapped(ShopProductCellViewModel model)
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
                return;
            }

            PlayerResourceModel resourceModel;
            if (model.DisplayCostType == DisplayCostType.Ad)
            {
                // ResourceType.IdleCoinのときProductResourceAmount見ると経過時間が表示されるので、明示的にDisplayAdvertisementResourceAmountを使う
                resourceModel = GetShopProductItemUseCase.GetPlayerResource(
                    model.ResourceType,
                    model.ResourceId,
                    model.DisplayAdvertisementResourceAmount());
            }
            else
            {
                resourceModel = GetShopProductItemUseCase.GetPlayerResource(
                    model.ResourceType,
                    model.ResourceId,
                    model.ProductResourceAmount);
            }
            if (resourceModel.IsEmpty()) return;

            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                resourceModel.Type,
                resourceModel.Id,
                resourceModel.Amount,
                ViewController);
        }

        void IShopCollectionViewDelegate.OnPurchaseButtonTapped(ShopProductCellViewModel model, UIIndexPath indexPath)
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
                return;
            }

            switch (model.DisplayCostType)
            {
                case DisplayCostType.Ad:
                    // 広告商品は確認を挟まず購入フェーズに行くので決定SE
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
                    ConfirmAdvertisementProductBuy(model.ProductId, indexPath, model.ProductName, model.PurchasableCount);
                    break;
                case DisplayCostType.Coin:
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    ConfirmProductBuyWithCoin(model.ProductId, indexPath);
                    break;
                case DisplayCostType.Diamond:
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    ConfirmProductBuyWithDiamond(model.ProductId, indexPath);
                    break;
                case DisplayCostType.Cash:
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    ConfirmProductBuyWithCash(model.ProductId, indexPath);
                    break;
            }
        }

        void IShopCollectionViewDelegate.OnPurchaseHistoryButtonTapped()
        {
            var vc = ViewFactory.Create<DiamondPurchaseHistoryViewController>();
            ViewController.PresentModally(vc);
        }

        void SetupProductList()
        {
            var shopViewModel = CreateShopViewModel();
            ViewController.SetShopViewModel(shopViewModel);
            ViewController.SetupShopDisplay();
        }

        ShopViewModel CreateShopViewModel()
        {
            var nextUpdateTimes = GetShopNextUpdateTimeUseCase.GetShopNextUpdateTimes();
            return ShopViewModelTranslator.Translate(
                GetStoreProductListUseCase.GetStoreProductList(),
                GetShopProductListUseCase.GetShopProductList(),
                nextUpdateTimes[DisplayShopProductType.Diamond],
                nextUpdateTimes[DisplayShopProductType.Daily],
                nextUpdateTimes[DisplayShopProductType.Weekly],
                nextUpdateTimes[DisplayShopProductType.Coin],
                GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo()
            );
        }

         void ConfirmAdvertisementProductBuy(MasterDataId productId,
             UIIndexPath indexPath,
             ProductName modelProductName,
             PurchasableCount modelPurchasableCount)
        {
            var confirmationModel = ConfirmAdvertisementProductBuyUseCase.ConfirmAdvertisementProductBuy(productId);
            var playerResourceModel = confirmationModel.ProductModel.ProductContents.FirstOrDefault();
            var playerResourceIconViewModel = playerResourceModel == null ?
                PlayerResourceIconViewModel.Empty :
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(playerResourceModel);

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

            if (ShouldShowAdvertising(confirmationModel.ProductModel.IsFirstTimeFreeDisplay.IsEnable()))
            {
                var vc = CreateInAppAdvertisingConfirmView(
                    productId,
                    indexPath,
                    modelProductName,
                    modelPurchasableCount,
                    playerResourceIconViewModel);
                ViewController.PresentModally(vc);
            }
            else
            {
                DoAsync.Invoke(ShopCancellationToken, ScreenInteractionControl, async ct =>
                {
                    await ExecBuyProductAndUpdateViews(productId, indexPath, ct);
                });
            }

        }

        InAppAdvertisingConfirmViewController CreateInAppAdvertisingConfirmView(
            MasterDataId productId,
            UIIndexPath indexPath,
            ProductName modelProductName,
            PurchasableCount modelPurchasableCount,
            PlayerResourceIconViewModel playerResourceIconViewModel)
        {
            //広告表示
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.Shop,
                modelProductName.Value,
                0,//未使用
                modelPurchasableCount.Value,
                () =>
                {
                    DoAsync.Invoke(ViewController.View, async ct =>
                    {
                        var result = await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.Shop, ct);
                        // 広告視聴キャンセルされたら何もしない
                        if(result == AdResultType.Cancelled) return;

                        await ExecBuyProductAndUpdateViews(productId, indexPath, ct);

                    });
                });
            vc.SetUpProductPlateComponent(playerResourceIconViewModel, modelProductName);
            return vc;
        }

        bool ShouldShowAdvertising(bool isFirstTimeFreeDisplay)
        {
            return !isFirstTimeFreeDisplay &&
                   GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty();
        }

        void ConfirmProductBuyWithCoin(MasterDataId productId, UIIndexPath indexPath)
        {
            var confirmationModel = ConfirmProductBuyWithCoinUseCase.ConfirmProductBuyWithCoin(productId);

            var viewModel =
                ProductBuyWithCoinConfirmationViewModelTranslator.ToProductBuyWithCoinConfirmationViewModel(confirmationModel);

            ShopConfirmViewUtil.ShowConfirmCoinCostProduct(
                ViewController,
                viewModel,
                () =>
                {
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

                    DoAsync.Invoke(ShopCancellationToken, ScreenInteractionControl, async ct =>
                    {
                        await ExecBuyProductAndUpdateViews(productId, indexPath, ct);
                    });
                });
        }


        void ConfirmProductBuyWithDiamond(MasterDataId productId, UIIndexPath indexPath)
        {
            var confirmationModel = ConfirmProductBuyWithDiamondUseCase.ConfirmProductBuyWithDiamond(productId);

            var viewModel =
                ProductBuyWithDiamondConfirmationViewModelTranslator.
                    ToProductBuyWithDiamondConfirmationViewModel(confirmationModel);

            ShopConfirmViewUtil.ShowDiamondBuyConfirmView(
                ViewController,
                viewModel,
                () =>
                {
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
                    DoAsync.Invoke(ShopCancellationToken, ScreenInteractionControl, async ct =>
                    {
                        await ExecBuyProductAndUpdateViews(productId, indexPath, ct);
                    });
                },
                OnTappedBoughtDiamondProductButton);
        }

        void ConfirmProductBuyWithCash(MasterDataId oprProductId,  UIIndexPath indexPath)
        {
            var confirmationModel = ConfirmProductBuyWithCashUseCase.ConfirmProductBuyWithCash(oprProductId);

            var viewModel =
                ProductBuyWithCashConfirmationViewModelTranslator.ToProductBuyWithCashConfirmationViewModel(confirmationModel);

            ShopConfirmViewUtil.ShowDiamondConfirmView(
                ViewController,
                viewModel,
                () =>
                {
                    var limitItems = LimitAmountModelCalculator
                        .FilteringLimitAmount(
                            confirmationModel.ProductModel.ProductContents.Select(i =>
                                new LimitCheckModel(i.Id, i.Type, i.Amount.Value)).ToList()
                        );
                    if (1 <= limitItems.Count)
                    {
                        // 有償石向け画面
                        LimitAmountWireframe.ShowPaidDiamondPurchaseLimitView();
                        return;
                    }

                    ExecPurchaseStoreProductAndUpdateViews(oprProductId, indexPath);
                });
        }

        async UniTask ExecBuyProductAndUpdateViews(MasterDataId productId, UIIndexPath indexPath, CancellationToken ct)
        {
            var viewModels = await _shopPresentationExecSupport.BuyProduct(ct, productId);

            CommonReceiveWireFrame.Show(viewModels);

            // 画面更新
            UpdatePurchasedProduct(indexPath);
            HomeFooterDelegate.UpdateBadgeStatus();
            HomeHeaderDelegate.UpdateStatus();
            ShopTabViewDelegate.UpdateShopTabBadge(true);
        }

        void ExecPurchaseStoreProductAndUpdateViews(MasterDataId productId, UIIndexPath indexPath)
        {
            InAppPurchaseExecuteDelegate.ExecutePurchase(ShopCancellationToken, async ct =>
            {
                await ShopPurchasePresentationHandler.PurchaseStoreProduct(ct, productId);

                UpdatePurchasedProduct(indexPath);
                HomeFooterDelegate.UpdateBadgeStatus();
                HomeHeaderDelegate.UpdateStatus();
                ShopTabViewDelegate.UpdateShopTabBadge(true);
            });
        }

        void OnTappedBoughtDiamondProductButton()
        {
            ViewController.MoveToDiamondSection();
        }


        void UpdatePurchasedProduct(UIIndexPath indexPath)
        {
            var shopViewModel = CreateShopViewModel();
            ViewController.SetShopViewModel(shopViewModel);
            ViewController.UpdatePurchasedProductUi(indexPath);
        }
    }
}
