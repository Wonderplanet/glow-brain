using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.View;
using GLOW.Scenes.Shop.Presentation.Translator;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Facade
{
    public class ShopBuyConfirmViewFacade : IShopBuyConfirmViewFacade
    {
        [Inject] IViewFactory ViewFactory { get; }

        public void ShowCoinBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithCoinConfirmationViewModel viewModel,
            Action onOk,
            Action onClose)
        {
            var argument = new CoinBuyConfirmViewController.Argument(viewModel, onOk, onClose);
            var controller = ViewFactory.Create<CoinBuyConfirmViewController, CoinBuyConfirmViewController.Argument>(argument);
            parent.PresentModally(controller);
        }

        public void ShowDiamondBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithDiamondConfirmationViewModel viewModel,
            bool isEnough,
            Action onOk,
            Action onClose)
        {
            var argument = new DiamondBuyConfirmViewController.Argument(viewModel, isEnough, onOk, onClose);
            var controller = ViewFactory.Create<DiamondBuyConfirmViewController,
                DiamondBuyConfirmViewController.Argument>(argument);
            parent.PresentModally(controller);
        }

        public void ShowCashBuyConfirmDialog(
            UIViewController parent,
            PackShopProductViewModel viewModel,
            Action onOk,
            Action onClose)
        {
            var translatedViewModel =
                ProductBuyWithCashConfirmationViewModelTranslator.ToProductBuyWithCashConfirmationViewModel(viewModel);
            var argument = new CashBuyConfirmViewController.Argument(translatedViewModel, onOk, onClose);
            var controller = ViewFactory.Create<CashBuyConfirmViewController, CashBuyConfirmViewController.Argument>(argument);
            parent.PresentModally(controller);
        }

        public void ShowPassCashBuyConfirmDialog(
            UIViewController parent,
            MasterDataId mstShopPassId,
            Action onOk,
            Action onClose)
        {
            var argument = new PassShopBuyConfirmViewController.Argument(mstShopPassId);
            var controller = ViewFactory.Create<PassShopBuyConfirmViewController, PassShopBuyConfirmViewController.Argument>(argument);
            controller.OnOkSelected = () =>
            {
                onOk?.Invoke();
            };

            parent.PresentModally(controller);
        }

        public void ShowCashBuyConfirmDialog(
            UIViewController parent,
            ProductBuyWithCashConfirmationViewModel viewModel,
            Action onOk,
            Action onClose)
        {
            var argument = new CashBuyConfirmViewController.Argument(viewModel, onOk, onClose);
            var controller = ViewFactory.Create<CashBuyConfirmViewController, CashBuyConfirmViewController.Argument>(argument);
            parent.PresentModally(controller);
        }
    }
}
