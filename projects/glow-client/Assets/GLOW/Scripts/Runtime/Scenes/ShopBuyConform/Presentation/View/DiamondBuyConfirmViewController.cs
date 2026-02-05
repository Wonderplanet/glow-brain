using System;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class DiamondBuyConfirmViewController : UIViewController<DiamondBuyConfirmView>
    {
        public record Argument(
            ProductBuyWithDiamondConfirmationViewModel ViewModel,
            bool IsEnough,
            Action OnOkSelected,
            Action OnCloseSelected);
        
        [Inject] IDiamondBuyConfirmViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public void SetViewModel(ProductBuyWithDiamondConfirmationViewModel viewModel, bool isEnough)
        {
            ActualView.Setup(viewModel, isEnough);
        }

        [UIAction]
        void OnSpecificCommerceSelected()
        {
            ViewDelegate.OnSpecificCommerceSelected();
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
