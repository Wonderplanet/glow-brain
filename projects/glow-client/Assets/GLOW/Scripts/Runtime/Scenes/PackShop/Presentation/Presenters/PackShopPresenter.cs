using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Presenters;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.PackShop.Domain.Models;
using GLOW.Scenes.PackShop.Domain.UseCase;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.PackShop.Presentation.Views;
using GLOW.Scenes.PackShopProductInfo.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.ShopTab.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PackShop.Presentation.Presenters
{
    public class PackShopPresenter : IPackShopViewDelegate
    {
        [Inject] PackShopViewController.Argument Argument { get; }
        [Inject] PackShopViewController ViewController { get; }
        [Inject] IShopTabBadgeControl ShopTabBadgeControl { get; }
        [Inject] GetPackProductListUseCase GetPackProductListUseCase { get; }
        [Inject] BuyPackShopProductUseCase BuyPackShopProductUseCase { get; }
        [Inject] ILimitAmountModelCalculator LimitAmountModelCalculator { get; }
        [Inject] GetRemainCountdownTimeUseCase GetRemainCountdownTimeUseCase { get; }
        [Inject] SavePackProductDisplayedFlagUseCase SavePackProductDisplayedFlagUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IShopConfirmViewUtil ShopConfirmViewUtil { get; }
        [Inject] ILimitAmountWireframe LimitAmountWireframe { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IInAppPurchaseExecuteDelegate InAppPurchaseExecuteDelegate { get; }
        [Inject] DailyRefreshWireFrame DailyRefreshWireFrame { get; }
        [Inject] DailyRefreshCheckUseCase DailyRefreshCheckUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] IShopTabViewDelegate ShopTabViewDelegate { get; }

        CancellationToken PackShopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();
        HeldAdSkipPassInfoViewModel _heldAdSkipPassInfoViewModel = HeldAdSkipPassInfoViewModel.Empty;


        void IPackShopViewDelegate.OnViewWillAppear()
        {
            _heldAdSkipPassInfoViewModel = HeldAdSkipPassInfoViewModelTranslator
                .ToHeldAdSkipPassInfoViewModel(GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo());

            DoAsync.Invoke(PackShopCancellationToken, async cancellationToken =>
            {
                await UpdateProductList(cancellationToken);
                // CollectionViewの初期化後にアニメーションを実行するためにずらす
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
                ViewController.PlayCellAppearanceAnimation();
            });
        }

        TimeSpan IPackShopViewDelegate.GetRemainCountDown(EndDateTime endTime)
        {
            return GetRemainCountdownTimeUseCase.GetRemainCountDown(endTime);
        }

        void IPackShopViewDelegate.OnBuyProductSelected(PackShopProductViewModel packViewModel)
        {
            if (DailyRefreshCheckUseCase.IsDailyRefreshTime())
            {
                DailyRefreshWireFrame.ShowTitleBackView();
                return;
            }

            switch (packViewModel.DisplayCostType)
            {
                case DisplayCostType.Cash:
                    ShowBuyPackWithCashConfirmView(packViewModel);
                    break;
                default:
                    LimitCheckAndBuyProduct(packViewModel);
                    break;
            }
        }

        void IPackShopViewDelegate.OnShowInfoSelected(MasterDataId oprProductId)
        {
            var argument = new PackShopProductInfoViewController.Argument(oprProductId, ViewController);
            var controller = ViewFactory.Create<
                PackShopProductInfoViewController,
                PackShopProductInfoViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        async UniTask UpdateProductList(CancellationToken cancellationToken)
        {
            var model = GetPackProductListUseCase.GetPackProductList();
            var viewModel = TranslateProductList(model);
            ViewController.SetupProductList(viewModel);

            // ステージ進捗パックのPageContent生成時にViewPortのストレッチ考慮のサイズが取得するため１フレーム待つ。
            if (!viewModel.StageClearPacks.IsEmpty())
            {
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
            }

            UpdateDisplayFlag(viewModel);
            ViewController.FocusTarget(viewModel, Argument.TargetId);

            if (viewModel.NormalPacks.IsEmpty()
                && viewModel.StageClearPacks.IsEmpty()
                && viewModel.DailyPacks.IsEmpty())
            {
                // 購入物がない場合はバッジを非表示にする
                ShopTabBadgeControl.ShowPackTabBadges(false);
            }
        }

        void UpdateDisplayFlag(PackShopProductListViewModel viewModel)
        {
            var packIds = viewModel.NormalPacks.Select(packViewModel => packViewModel.OprProductId).ToList();
            packIds.AddRange(viewModel.DailyPacks.Select(packViewModel => packViewModel.OprProductId));
            packIds.AddRange(viewModel.StageClearPacks.Select(packViewModel => packViewModel.OprProductId));
            SavePackProductDisplayedFlagUseCase.SaveDisplayFlag(packIds);
        }

        PackShopProductListViewModel TranslateProductList(PackShopProductListModel model)
        {
            var normalPacks = model.NormalPacks
                .Select(TranslateProduct)
                .ToList();
            var dailyPacks = model.DailyPacks
                .Select(TranslateProduct)
                .ToList();
            var stageClearPacks = model.StageClearPacks
                .Select(TranslateProduct)
                .ToList();

            return new PackShopProductListViewModel(normalPacks, dailyPacks, stageClearPacks, model.RemainingDailyPackTime);
        }

        PackShopProductViewModel TranslateProduct(PackShopProductModel model)
        {
            var items = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Items);
            return new PackShopProductViewModel(
                model.OprProductId,
                model.NewFlag,
                model.ProductName,
                model.ProductPriceType,
                model.ProductPrice,
                model.RawProductPriceText,
                model.DiscountRate,
                model.PurchasableCount,
                model.EndDateTime,
                items,
                model.BannerAssetPath,
                model.Decoration,
                _heldAdSkipPassInfoViewModel,
                model.IsFirstTimeFreeDisplay);
        }

        void ShowBuyPackWithCashConfirmView(PackShopProductViewModel packViewModel)
        {
            ShopConfirmViewUtil.ShowPackConfirmView(
                ViewController,
                packViewModel,
                () => LimitCheckAndBuyProduct(packViewModel));
        }

        void LimitCheckAndBuyProduct(PackShopProductViewModel packViewModel)
        {
            // 所持上限チェック
            var limitCheckModels = packViewModel.Items
                .Select(i => new LimitCheckModel(i.Id, i.ResourceType, i.Amount.Value))
                .ToList();

            var limitItems = LimitAmountModelCalculator
                .FilteringLimitAmount(limitCheckModels)
                .ToList();

            if (limitItems.Any(i => i.ResourceType != ResourceType.PaidDiamond))
            {
                LimitAmountWireframe.ShowItemPurchaseLimitView();
                return;
            }

            // 有償石所持上限時
            if (limitItems.Any(i => i.ResourceType == ResourceType.PaidDiamond))
            {
                LimitAmountWireframe.ShowPaidDiamondPurchaseLimitView();
                return;
            }

            DoAsync.Invoke(ViewController.View.gameObject, ScreenInteractionControl, async cancellationToken =>
            {
                if (packViewModel.DisplayCostType == DisplayCostType.Ad
                    && _heldAdSkipPassInfoViewModel.IsEmpty())
                {
                    var result =
                        await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.IdleIncentive,
                            cancellationToken);
                    // 広告キャンセルされたら何もしない
                    if (result == AdResultType.Cancelled) return;
                }

                BuyProduct(packViewModel);
            });
        }

        void BuyProduct(PackShopProductViewModel packViewModel)
        {
            InAppPurchaseExecuteDelegate.ExecutePurchase(
                PackShopCancellationToken,
                async ct =>
                {
                    try
                    {
                        var models = await BuyPackShopProductUseCase.BuyProduct(
                            ct,
                            packViewModel.OprProductId);

                        // ヘッダー更新
                        HomeHeaderDelegate.UpdateStatus();
                        // タブ更新
                        ShopTabViewDelegate.UpdatePackTabBadge();

                        var viewModels = models
                            .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                            .ToList();

                        CommonReceiveWireFrame.Show(viewModels);
                        UpdateProductList(ct).Forget();
                        ;
                    }
                    catch (MstNotFoundException)
                    {
                        MessageViewUtil.ShowMessageWithClose(
                            "確認",
                            "このパックの販売は終了しました。",
                            onClose: () =>
                            {
                                UpdateProductList(ct).Forget();
                            });
                    }
                });
        }
    }
}
