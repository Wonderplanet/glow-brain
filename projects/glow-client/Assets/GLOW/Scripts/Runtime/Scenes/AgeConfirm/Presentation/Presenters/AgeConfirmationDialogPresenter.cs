using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.Presenters
{
    /// <summary>
    /// 74-1_年齢確認
    /// </summary>
    public class AgeConfirmationDialogPresenter : IAgeConfirmationDialogViewDelegate
    {
        const int CloseDelayMilliseconds = 40;
        [Inject] AgeConfirmationDialogViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
            ViewController.OnAgeConfirmCanceled?.Invoke();
        }

        public void OnOKButtonTapped()
        {
            // 生年月日の確認ダイアログを表示
            var dateOfBirth = new DateOfBirth(ViewController.ActualView.InputText);
            var argument = new InputtedAgeConfirmationDialogViewController.Argument(dateOfBirth);
            var controller = ViewFactory.Create<
                InputtedAgeConfirmationDialogViewController, 
                InputtedAgeConfirmationDialogViewController.Argument>(argument);
            
            controller.OnConfirmed = OnConfirmed;
            controller.OnValidationError = OnValidationError;
            
            ViewController.PresentModally(controller);
        }

        public void OnTermsOfService()
        {
            OpenURL(Credentials.TermsOfServiceURL);
        }

        void OnConfirmed()
        {
            ViewController.Dismiss();
            ViewController.OnAgeConfirmEnded?.Invoke();
        }
        
        void OnValidationError()
        {
            // エラーダイアログ遷移
            var errorDialog = ViewFactory.Create<InputtedAgeErrorDialogViewController>();
            ViewController.PresentModally(errorDialog);
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
