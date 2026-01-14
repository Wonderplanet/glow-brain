using GLOW.Modules.AgreementExclusionRegex.Domain;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.PrivacyOptionDialog.Domain.UseCases;
using GLOW.Scenes.PrivacyOptionDialog.Presentation.Views;
using UnityEngine;
using UnityHTTPLibrary;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.PrivacyOptionDialog.Presentation.Presenters
{
    public class PrivacyOptionDialogPresenter : IPrivacyOptionDialogViewDelegate
    {
        [Inject] PrivacyOptionDialogViewController ViewController { get; }
        [Inject] PrivacyOptionDialogViewController.Argument Argument { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        void IPrivacyOptionDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUrl(Argument.Url, AgreementExclusionRegexGenerator.GenerateExclusionRegex());
        }

        void IPrivacyOptionDialogViewDelegate.OnWebViewHooked(string url)
        {
            if (IsContainsRedirect(url))
            {
                ViewController.Close();
                CommonToastWireFrame.ShowScreenCenterToast("同意を更新しました");
            }
            else
            {
                // 他のリンクページに遷移した場合は、ブラウザで開く
                Application.OpenURL(url);
            }
        }

        void IPrivacyOptionDialogViewDelegate.OnWebViewError(string msg)
        {
            if (IsContainsRedirect(msg)) return;

            // エラーが発生した場合、進行不能にならないようにCloseを呼び出す
            ViewController.Close();
            MessageViewUtil.ShowMessageWithOk(
                "通信エラー",
                "通信エラーが発生しました。\n\n時間を置いてから再度お試しください。");
        }

        bool IsContainsRedirect(string url)
        {
            return url.Contains("DummyAgreementRedirect");
        }
    }
}
