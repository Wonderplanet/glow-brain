using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public interface IShopConfirmViewUtil
    {
        void ShowPackConfirmView(
            UIViewController parent,
            PackShopProductViewModel model,
            Action onOk);

        void ShowPassConfirmView(
            UIViewController parent,
            MasterDataId mstShopPassId,
            DisplayCostType displayCostType,
            Action onOk);

        void ShowDiamondBuyConfirmView(
            UIViewController parent,
            ProductBuyWithDiamondConfirmationViewModel viewModel,
            Action onOk,
            Action moveShopViewScrollAction);

        void ShowDiamondConfirmView(
            UIViewController parent,
            ProductBuyWithCashConfirmationViewModel viewModel,
            Action onOk);

        void ShowConfirmCoinCostProduct(UIViewController parent, ProductBuyWithCoinConfirmationViewModel viewModel, Action onOk);
    }
}
