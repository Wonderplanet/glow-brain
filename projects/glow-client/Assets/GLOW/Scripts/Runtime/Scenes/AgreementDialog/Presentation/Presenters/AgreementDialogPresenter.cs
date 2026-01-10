using GLOW.Modules.AgreementExclusionRegex.Domain;
using GLOW.Scenes.AgreementDialog.Presentation.Views;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.AgreementDialog.Presentation.Presenters
{
    public class AgreementDialogPresenter : IAgreementDialogViewDelegate
    {
        [Inject] AgreementDialogViewController ViewController { get; }
        [Inject] AgreementDialogViewController.Argument Argument { get; set; }

        void IAgreementDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUrl(Argument.Url, AgreementExclusionRegexGenerator.GenerateExclusionRegex());
        }

        void IAgreementDialogViewDelegate.OnWebViewHooked(string url)
        {
            if (url.Contains("DummyAgreementRedirect"))
            {
                ViewController.Close();
            }
            else
            {
                // 他のリンクページに遷移した場合は、ブラウザで開く
                Application.OpenURL(url);
            }
        }

        void IAgreementDialogViewDelegate.OnWebViewError()
        {
            // エラーが発生した場合、進行不能にならないようにCloseを呼び出す
            ViewController.Close();
        }
    }
}
