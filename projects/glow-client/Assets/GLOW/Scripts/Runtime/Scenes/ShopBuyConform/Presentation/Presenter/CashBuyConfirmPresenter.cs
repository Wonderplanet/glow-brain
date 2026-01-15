using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Presenter
{
    public class CashBuyConfirmPresenter : ICashBuyConfirmViewDelegate
    {
        [Inject] CashBuyConfirmViewController ViewController { get; }
        [Inject] CashBuyConfirmViewController.Argument Argument { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnViewDidLoad));

            ViewController.SetViewModel(Argument.ViewModel);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnViewDidUnload));
        }

        public void OnSpecificCommerceSelected()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnSpecificCommerceSelected));

            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        public void OnFundsSettlementSelected()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnFundsSettlementSelected));

            CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement);
        }

        public void OnBuySelected()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnBuySelected));
            ViewController.Dismiss(completion:Argument.OnOkSelected);
        }

        public void OnCloseSelected()
        {
            ApplicationLog.Log(nameof(CashBuyConfirmPresenter), nameof(OnCloseSelected));

            ViewController.Dismiss(completion:Argument.OnCloseSelected);
        }
    }
}
