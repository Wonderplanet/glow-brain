using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Presenter
{
    public class DiamondBuyConfirmPresenter : IDiamondBuyConfirmViewDelegate
    {
        [Inject] DiamondBuyConfirmViewController ViewController { get; }
        [Inject] DiamondBuyConfirmViewController.Argument Argument { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(DiamondBuyConfirmPresenter), nameof(OnViewDidLoad));

            SetViewModel(Argument.ViewModel, Argument.IsEnough);
        }

        public void OnSpecificCommerceSelected()
        {
            ApplicationLog.Log(nameof(DiamondBuyConfirmPresenter), nameof(OnSpecificCommerceSelected));

            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        public void OnBuySelected()
        {
            ApplicationLog.Log(nameof(DiamondBuyConfirmPresenter), nameof(OnBuySelected));

            ViewController.Dismiss(completion:Argument.OnOkSelected);
        }

        public void OnCloseSelected()
        {
            ApplicationLog.Log(nameof(DiamondBuyConfirmPresenter), nameof(OnCloseSelected));

            ViewController.Dismiss(completion : Argument.OnCloseSelected);
        }

        void SetViewModel(ProductBuyWithDiamondConfirmationViewModel viewModel, bool isEnough)
        {
            ViewController.SetViewModel(viewModel, isEnough);
        }
    }
}
