using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Views;
using GLOW.Scenes.AppAppliedBalanceDialog.Presentation;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.OtherMenu.Domain;
using GLOW.Scenes.PrivacyOptionDialog.Domain.UseCases;
using GLOW.Scenes.PrivacyOptionDialog.Presentation.Views;
using GLOW.Scenes.PurchaseLimitDialog.Presentation;
using UnityHTTPLibrary;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.OtherMenu.Presentation
{
    public class OtherMenuPresenter : IOtherMenuViewDelegate
    {
        const int CloseDelayMilliseconds = 40;

        [Inject] OtherMenuViewController ViewController { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] PrivacyOptionDialogConsentRequestUseCase PrivacyOptionDialogConsentRequestUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void OnTermsOfService()
        {
            OpenURL(Credentials.TermsOfServiceURL);
        }

        public void OnPrivacyPolicy()
        {
            OpenURL(Credentials.PrivacyPolicyURL);
        }

        public void OnInAppAdvertisement()
        {
            OpenURL(Credentials.InAppAdvertisementUrl);
        }

        public void OnPrivacyOption()
        {
            // APIのエラーがWebViewの裏に表示されるためPresentModally前にAPIを呼ぶ
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    var url = await PrivacyOptionDialogConsentRequestUseCase.GetConsentRequestUrl(cancellationToken);

                    var args = new PrivacyOptionDialogViewController.Argument(url);
                    var privacyOptionDialogViewController = ViewFactory
                        .Create<PrivacyOptionDialogViewController, PrivacyOptionDialogViewController.Argument>(args);
                    ViewController.PresentModally(privacyOptionDialogViewController);
                }
                catch (ServerErrorException se)
                {
                    MessageViewUtil.ShowMessageWithOk(
                        "通信エラー",
                        "通信エラーが発生しました。\n\n時間を置いてから再度お試しください。");
                }
            });
        }

        public void OnCopyright()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.PluginLicenses);
        }

        public void OnFundSettlement()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement);
        }

        public void OnSpecificCommerce()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        public void OnPurchaseLimit()
        {
            var purchaseLimitViewController = ViewFactory.Create<PurchaseLimitDialogViewController>();
            ViewController.PresentModally(purchaseLimitViewController);
        }

        public void OnAppAppliedBalance()
        {
            var appliedBalanceViewController = ViewFactory.Create<AppAppliedBalanceDialogViewController>();
            ViewController.PresentModally(appliedBalanceViewController);
        }

        public void OnAccountDelete()
        {
            var accountDeleteConfirmDialogViewController = ViewFactory.Create<AccountDeleteConfirmDialogViewController>();
            ViewController.PresentModally(accountDeleteConfirmDialogViewController);
        }

        public void OnCloseSelected()
        {
            ViewController.Dismiss();
        }


        void OpenURL(string url)
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                ViewController.ActualView.UserInteraction = false;
                await UniTask.Delay(CloseDelayMilliseconds, cancellationToken: cancellationToken);
                CustomOpenURL.OpenURL(url);
                ViewController.ActualView.UserInteraction = true;
            });
        }
    }
}
