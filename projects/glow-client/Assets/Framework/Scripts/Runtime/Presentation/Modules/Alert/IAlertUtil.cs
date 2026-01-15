using System;

namespace WPFramework.Presentation.Modules
{
    public interface IAlertUtil
    {
        void ShowAlert(
            string title,
            string message,
            Action onOk = null,
            bool enableEscape = true,
            bool enableOptionalDestructive = false,
            string prefabName = null);

        void ShowAlertWithOption(
            string title,
            string message,
            string buttonTitle,
            Action onOk = null,
            bool enableEscape = true,
            bool enableOptionalDestructive = false,
            string prefabName = null);

        void ShowAlertOkEscapeBind(
            string title,
            string message,
            Action onOk,
            bool enableOptionalDestructive = false,
            string prefabName = null);

        void ShowAlertConfirm(
            string title,
            string message,
            Action onOk,
            Action onCancel = null,
            bool enableOptionalDestructive = false);

        void ShowAlertConfirmWithOption(
            string title,
            string message,
            string option1ButtonTitle,
            string option2ButtonTitle,
            Action onOption1,
            Action onOption2,
            bool enableEscape = true,
            bool enableOptionalDestructive = false,
            string prefabName = null);

        void ShowAlertConfirmWithOption(
            string title,
            string message,
            string option1ButtonTitle,
            string option2ButtonTitle,
            string option3ButtonTitle,
            Action onOption1,
            Action onOption2,
            Action onOption3,
            bool enableEscape = true,
            bool enableOptionalDestructive = false,
            string prefabName = null);
    }
}
