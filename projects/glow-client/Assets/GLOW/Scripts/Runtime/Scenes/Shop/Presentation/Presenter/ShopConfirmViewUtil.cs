using System;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Modules;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.Shop.Domain.Model;
using GLOW.Scenes.Shop.Domain.UseCase;
using GLOW.Scenes.Shop.Domain.ValueObjects;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.Facade;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Shop.Presentation.Presenter
{
    public class ShopConfirmViewUtil : IShopConfirmViewUtil
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        [Inject] CalculateCostEnoughUseCase CalculateCostEnoughUseCase { get; }

        [Inject] IShopBuyConfirmViewFacade ShopBuyConfirmViewFacade { get; }

        [Inject] ParentalConsentIfMinorUseCase ParentalConsentIfMinorUseCase { get; }

        [Inject] UserStoreInfoUseCase UserStoreInfoUseCase { get; }

        [Inject] IViewFactory ViewFactory { get; }

        [Inject] CheckShopPurchaseLimitUseCase CheckShopPurchaseLimitUseCase { get; }

        void IShopConfirmViewUtil.ShowPackConfirmView(
            UIViewController parent,
            PackShopProductViewModel model,
            Action onOk)
        {
            DoAsync.Invoke(parent.View, async cancellationToken =>
            {
                var confirmed = await ConfirmStoreProductPurchase(
                    parent,
                    cancellationToken,
                    model.OprProductId,
                    ShopPassFlag.False,
                    model.DisplayCostType);
                if (confirmed)
                {
                    ShopBuyConfirmViewFacade.ShowCashBuyConfirmDialog(parent, model, onOk, () => { });
                }
            });
        }

        void IShopConfirmViewUtil.ShowPassConfirmView(
            UIViewController parent,
            MasterDataId mstShopPassId,
            DisplayCostType displayCostType,
            Action onOk)
        {
            DoAsync.Invoke(parent.View, async cancellationToken =>
            {
                var confirmed = await ConfirmStoreProductPurchase(
                    parent,
                    cancellationToken,
                    mstShopPassId,
                    ShopPassFlag.True,
                    displayCostType);
                if (confirmed)
                {
                    ShopBuyConfirmViewFacade.ShowPassCashBuyConfirmDialog(parent, mstShopPassId, onOk, () => { });
                }
            });
        }

        void IShopConfirmViewUtil.ShowDiamondBuyConfirmView(
            UIViewController parent,
            ProductBuyWithDiamondConfirmationViewModel viewModel,
            Action onOk,
            Action moveShopViewScrollAction)
        {
            if(viewModel.IsFirstTimeFreeDisplay.IsEnable())
            {
                ShowConfirmFreeProduct(viewModel.ProductName, onOk);
                return;
            }

            var calculateCostEnoughUseCaseModel = CalculateCostEnoughUseCase.CalculateCostEnough(
                DisplayCostType.Diamond,
                viewModel.CostAmount);

            var onOkAction = new Action(() =>
            {
                if (!calculateCostEnoughUseCaseModel.IsEnough)
                {
                    NotEnoughDiamondCostDialog(
                        DisplayCostType.Diamond,
                        viewModel.CostAmount,
                        calculateCostEnoughUseCaseModel,
                        moveShopViewScrollAction);
                    return;
                }

                onOk();
            });

            ShopBuyConfirmViewFacade.ShowDiamondBuyConfirmDialog(
                parent,
                viewModel,
                calculateCostEnoughUseCaseModel.IsEnough,
                onOkAction,
                () => { });
        }

        void IShopConfirmViewUtil.ShowDiamondConfirmView(
            UIViewController parent,
            ProductBuyWithCashConfirmationViewModel viewModel,
            Action onOk)
        {
            if(viewModel.IsFirstTimeFreeDisplay.IsEnable())
            {
                ShowConfirmFreeProduct(viewModel.ProductName, onOk);
                return;
            }

            DoAsync.Invoke(parent.View, async cancellationToken =>
            {
                var confirmed = await ConfirmStoreProductPurchase(
                    parent,
                    cancellationToken,
                    viewModel.OprProductId,
                    ShopPassFlag.False,
                    viewModel.DisplayCostType);
                if (confirmed)
                {
                    ShopBuyConfirmViewFacade.ShowCashBuyConfirmDialog(
                        parent,
                        viewModel,
                        onOk,
                        () => { });
                }
            });
        }

        void IShopConfirmViewUtil.ShowConfirmCoinCostProduct(
            UIViewController parent,
            ProductBuyWithCoinConfirmationViewModel viewModel,
            Action onOk)
        {
            if(viewModel.IsFirstTimeFreeDisplay.IsEnable())
            {
                ShowConfirmFreeProduct(viewModel.ProductName, onOk);
                return;
            }

            var calculateCostEnoughUseCaseModel = CalculateCostEnoughUseCase.CalculateCostEnough(
                DisplayCostType.Coin,
                viewModel.CostAmount);

            ShopBuyConfirmViewFacade.ShowCoinBuyConfirmDialog(
                parent,
                viewModel,
                () =>
            {
                if (!calculateCostEnoughUseCaseModel.IsEnough)
                {
                    NotEnoughCoinCostDialog(
                        DisplayCostType.Coin,
                        viewModel.CostAmount,
                        calculateCostEnoughUseCaseModel);
                    return;
                }

                onOk();
            }, () => { });
        }

        void ShowConfirmFreeProduct(ProductName productName, Action onOk)
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                "獲得確認",
                $"{productName.Value}\nを無料で獲得することができます。",
                "",
                "獲得",
                "キャンセル",
                onOk);
        }

        void NotEnoughCoinCostDialog(
            DisplayCostType displayCostType,
            CostAmount costAmount,
            CalculateCostEnoughUseCaseModel calculateCostEnoughUseCaseModel)
        {
            MessageViewUtil.ShowMessageWithClose(
                "交換確認",
                NotEnoughCostDialogMessage(displayCostType, costAmount, calculateCostEnoughUseCaseModel),
                "",
                () => { });
        }

        void NotEnoughDiamondCostDialog(
            DisplayCostType displayCostType,
            CostAmount costAmount,
            CalculateCostEnoughUseCaseModel calculateCostEnoughUseCaseModel,
            Action moveShopViewScrollAction)
        {
            MessageViewUtil.ShowMessageWith2Buttons("購入確認",
                NotEnoughCostDialogMessage(displayCostType, costAmount, calculateCostEnoughUseCaseModel),
                "",
                "購入",
                "キャンセル",
                moveShopViewScrollAction,
                () => { });
        }

        string NotEnoughCostDialogMessage(
            DisplayCostType displayCostType,
            CostAmount costAmount,
            CalculateCostEnoughUseCaseModel costEnoughModel)
        {
            var message = "";
            if (displayCostType == DisplayCostType.Coin)
            {
                var notEnoughCostValue = Mathf.Abs(costEnoughModel.CurrentResourceAmount - costAmount.Value);
                var displayNotEnoughCostValue = new CostAmount((int)notEnoughCostValue);
                // 足りないコストの部分のみ文字色を変える仕様のため、TextMeshProのRich Textのcolorタグで対応
                message = ZString.Format("交換するのにコインが<color={0}>{1}個足りません。</color>",
                    ColorCodeTheme.TextRed,
                    displayNotEnoughCostValue);
            }

            if (displayCostType == DisplayCostType.Diamond)
            {
                var notEnoughCostValue = Mathf.Abs(costEnoughModel.CurrentResourceAmount - costAmount.Value);
                var displayNotEnoughCostValue = new CostAmount((int)notEnoughCostValue);
                // 足りないコストの部分のみ文字色を変える仕様のため、TextMeshProのRich Textのcolorタグで対応
                message = ZString.Format("購入するのにプリズムが<color={0}>{1}個足りません。</color>\nプリズムを購入しますか？",
                    ColorCodeTheme.TextRed,
                    displayNotEnoughCostValue);
            }
            return message;
        }

        /// <summary>
        /// 課金商品の購入確認処理
        /// </summary>
        async UniTask<bool> ConfirmStoreProductPurchase(
            UIViewController parent,
            CancellationToken cancellationToken,
            MasterDataId targetId,
            ShopPassFlag isShopPass,
            DisplayCostType displayCostType)
        {
            // Cash以外は購入確認不要
            if (displayCostType != DisplayCostType.Cash)
            {
                return true;
            }

            var ageConfirmed = await ShowAgeConfirmationIfNeeded(parent, cancellationToken);
            if (!ageConfirmed) return false;

            // 購入限度額確認
            var isOverShopPurchaseLimit = CheckShopPurchaseLimitUseCase.CheckShopPurchaseLimit(
                targetId,
                isShopPass);

            if (isOverShopPurchaseLimit)
            {
                ShopPurchaseLimitDialogHelper.ShowDialog(MessageViewUtil);
                return false;
            }

            return await ShowParentalConsentDialogIfNeeded(cancellationToken);
        }

        async UniTask<bool> ShowAgeConfirmationIfNeeded(UIViewController parent, CancellationToken cancellationToken)
        {
            var userStoreInfoModel = UserStoreInfoUseCase.GetUserStoreInfoModel();
            if (userStoreInfoModel.UserAge.IsEmpty())
            {
                var controller = ViewFactory.Create<AgeConfirmationDialogViewController>();
                var tcs = new UniTaskCompletionSource<bool>();

                controller.OnAgeConfirmEnded = () => tcs.TrySetResult(true);
                controller.OnAgeConfirmCanceled = () => tcs.TrySetResult(false);

                parent.PresentModally(controller);
                return await tcs.Task.AttachExternalCancellation(cancellationToken);
            }
            return true;
        }

        async UniTask<bool> ShowParentalConsentDialogIfNeeded(CancellationToken cancellationToken)
        {
            if(ParentalConsentIfMinorUseCase.GetShouldShowParentalConsentFlag().IsTrue())
            {
                var tcs = new UniTaskCompletionSource<bool>();

                MessageViewUtil.ShowMessageWith2Buttons(
                    "購入確認",
                    "未成年の方がアプリ専用通貨等を購入される場合には、法定代理人（ご両親等）の同意を得てから購入してください。\n\n※ないようがわからないときは、\nおうちのひとにがめんをみせてください。",
                    string.Empty,
                    "同意を得て購入",
                    "購入しない",
                    () => tcs.TrySetResult(true),
                    () => tcs.TrySetResult(false));

                return await tcs.Task.AttachExternalCancellation(cancellationToken);
            }
            return true;
        }
    }
}
