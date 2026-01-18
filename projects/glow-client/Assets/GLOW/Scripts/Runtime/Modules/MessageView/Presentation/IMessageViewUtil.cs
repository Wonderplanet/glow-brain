using System;

namespace GLOW.Modules.MessageView.Presentation
{
    public interface IMessageViewUtil
    {
        MessageViewController ShowMessageWithOk(
            string title,
            string message,
            string attentionMessage = null,
            Action onOk = null,
            bool enableEscape = true,
            string prefabName = null);

        MessageViewController ShowMessageWithClose(
            string title,
            string message,
            string attentionMessage = null,
            Action onClose = null,
            bool enableEscape = true,
            string prefabName = null);

        MessageViewController ShowConfirmMessage(
            string title,
            string message,
            string attentionMessage = null,
            Action onOk = null,
            Action onCancel = null,
            bool enableEscape = true,
            string prefabName = null);

        MessageViewController ShowMessageWithButton(
            string title,
            string message,
            string attentionMessage,
            string buttonTitle,
            Action onOk = null,
            bool enableEscape = true,
            string prefabName = null);

        MessageViewController ShowMessageWith2Buttons(
            string title,
            string message,
            string attentionMessage,
            string option1ButtonTitle,
            string option2ButtonTitle,
            Action action1 = null,
            Action action2 = null,
            Action onClose = null,
            bool enableEscape = true,
            string prefabName = null);

        MessageViewController ShowMessageWith3Buttons(
            string title,
            string message,
            string attentionMessage,
            string option1ButtonTitle,
            string option2ButtonTitle,
            string option3ButtonTitle,
            Action onOption1,
            Action onOption2,
            Action onOption3,
            Action onClose,
            bool enableEscape = true,
            string prefabName = null);
    }
}
