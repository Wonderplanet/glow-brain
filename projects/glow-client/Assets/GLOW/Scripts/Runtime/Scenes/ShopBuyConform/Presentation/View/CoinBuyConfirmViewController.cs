using System;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class CoinBuyConfirmViewController : UIViewController<CoinBuyConfirmView>
    {
        public record Argument(ProductBuyWithCoinConfirmationViewModel ViewModel, Action OnOkSelected, Action OnCloseSelected);

        [Inject] ICoinBuyConfirmViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void SetViewModel(ProductBuyWithCoinConfirmationViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnBuySelected()
        {
            ViewDelegate.OnTradeSelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }

    }
}
