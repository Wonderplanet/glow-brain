using System;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class ExchangeConfirmViewController : UIViewController<ExchangeConfirmView>
    {
        public record Argument(ExchangeConfirmViewModel ViewModel, Action OnExchangeSelected, Action OnCancelSelected);

        [Inject] IExchangeConfirmViewDelegate ViewDelegate { get; }

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

        public void Setup(ExchangeConfirmViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        [UIAction]
        void OnExchangeSelected()
        {
            ViewDelegate.OnExchangeSelected();
        }

        [UIAction]
        void OnCancelSelected()
        {
            ViewDelegate.OnCancelSelected();
        }
    }
}
