using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AgeConfirm.Presentation.View
{
    /// <summary>
    /// 74-1_年齢確認
    /// 　74-1-1_年齢入力確認ダイアログ
    /// </summary>
    public class InputtedAgeConfirmationDialogView : UIView
    {
        [SerializeField] UIText _inputtedDateOfBirthText;

        public void SetInputtedDateOfBirthText(DateOfBirth dateOfBirth)
        {
            _inputtedDateOfBirthText.SetText(dateOfBirth.ToString());
        }
    }
}
