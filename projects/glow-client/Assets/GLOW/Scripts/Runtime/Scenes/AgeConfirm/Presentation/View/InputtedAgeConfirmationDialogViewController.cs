using System;
using GLOW.Core.Domain.ValueObjects;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Presentation.View
{
    /// <summary>
    /// 74-1_年齢確認
    /// 　74-1-1_年齢入力確認ダイアログ
    /// </summary>
    public class InputtedAgeConfirmationDialogViewController : UIViewController<InputtedAgeConfirmationDialogView>
    {
        [Inject] IInputtedAgeConfirmationDialogViewDelegate Delegate { get; }
        public record Argument(DateOfBirth DateOfBirth);

        public Action OnConfirmed { get; set; }
        public Action OnValidationError { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            Delegate.OnViewDidLoad();
        }

        public void SetInputtedDateOfBirthText(DateOfBirth dateOfBirth)
        {
            ActualView.SetInputtedDateOfBirthText(dateOfBirth);
        }

        [UIAction]
        void OnOkButton()
        {
            Delegate.OnOKButtonTapped();
        }

        [UIAction]
        void OnCancelButton()
        {
            Delegate.OnCloseButtonTapped();
        }
    }
}
