using System;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class CashBuyConfirmViewController : UIViewController<CashBuyConfirmView>
    {
        public record Argument(ProductBuyWithCashConfirmationViewModel ViewModel, Action OnOkSelected, Action OnCloseSelected);

        [Inject] ICashBuyConfirmViewDelegate ViewDelegate { get; }

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

        public void SetViewModel(ProductBuyWithCashConfirmationViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnSpecificCommerceSelected()
        {
            ViewDelegate.OnSpecificCommerceSelected();
        }

        [UIAction]
        void OnFundsSettlementSelected()
        {
            ViewDelegate.OnFundsSettlementSelected();
        }

        [UIAction]
        void OnBuySelected()
        {
            ViewDelegate.OnBuySelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
