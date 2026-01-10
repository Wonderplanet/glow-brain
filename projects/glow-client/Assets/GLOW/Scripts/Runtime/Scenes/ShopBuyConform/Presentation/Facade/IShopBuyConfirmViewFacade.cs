using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Facade
{
    public interface IShopBuyConfirmViewFacade
    {
        void ShowCoinBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithCoinConfirmationViewModel viewModel,
            Action onOk,
            Action onClose);

        void ShowDiamondBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithDiamondConfirmationViewModel viewModel,
            bool isEnough,
            Action onOk,
            Action onClose);

        void ShowCashBuyConfirmDialog(
            UIViewController parent,
            PackShopProductViewModel viewModel,
            Action onOk,
            Action onClose);

        void ShowPassCashBuyConfirmDialog(
            UIViewController parent,
            MasterDataId mstShopPassId,
            Action onOk,
            Action onClose);

        void ShowCashBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithCashConfirmationViewModel viewModel,
            Action onOk,
            Action onClose);
    }
}
