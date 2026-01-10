using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.PassShopBuyConfirm.Domain.UseCase;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.Translator;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.View;
using Zenject;

namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.Presenter
{
    public class PassShopBuyConfirmPresenter : IPassShopBuyConfirmViewDelegate
    {
        [Inject] PassShopBuyConfirmViewController ViewController { get; }
        [Inject] PassShopBuyConfirmViewController.Argument Argument { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] ShowPassBuyConfirmUseCase ShowPassBuyConfirmUseCase { get; }
        
        void IPassShopBuyConfirmViewDelegate.OnViewDidLoad()
        {
            var model = ShowPassBuyConfirmUseCase.GetPassShopConfirmModel(Argument.MstShopPassId);
            var viewModel = PassShopBuyConfirmViewModelTranslator.ToPassShopBuyConfirmViewModel(model);
            ViewController.SetUpViewUi(viewModel);
        }

        void IPassShopBuyConfirmViewDelegate.OnCloseSelected()
        {
            ViewController.Dismiss();
        }

        void IPassShopBuyConfirmViewDelegate.OnBuySelected()
        {
            ViewController.Dismiss(completion : ViewController.OnOkSelected);
        }

        void IPassShopBuyConfirmViewDelegate.ShowSpecificCommerce()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IPassShopBuyConfirmViewDelegate.ShowFundsSettlement()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement);
        }
    }
}