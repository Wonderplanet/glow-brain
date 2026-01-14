using GLOW.Core.Exceptions;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.Presenters
{
    /// <summary>
    /// 74-1_年齢確認
    /// 　74-1-1_年齢入力確認ダイアログ
    /// </summary>
    public class InputtedAgeConfirmationDialogPresenter : IInputtedAgeConfirmationDialogViewDelegate
    {
        [Inject] InputtedAgeConfirmationDialogViewController ViewController { get;}
        [Inject] InputtedAgeConfirmationDialogUseCase UseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] InputtedAgeConfirmationDialogViewController.Argument Argument { get; }

        public void OnViewDidLoad()
        {
            ViewController.SetInputtedDateOfBirthText(Argument.DateOfBirth);
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        public void OnOKButtonTapped()
        {
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                try
                {
                    // 通信処理を行い、結果に応じて画面遷移を行う
                    await UseCase.SetUserAge(ct, Argument.DateOfBirth);

                    ViewController.Dismiss();
                    ViewController.OnConfirmed?.Invoke();
                }
                catch (ValidationErrorException)
                {
                    // エラーダイアログ遷移
                    ViewController.Dismiss();
                    ViewController.OnValidationError?.Invoke();
                }
                catch (InvalidParameterException)
                {
                    // エラーダイアログ遷移
                    ViewController.Dismiss();
                    ViewController.OnValidationError?.Invoke();
                }
            });
        }
    }
}
