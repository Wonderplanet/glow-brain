using System;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.MessageView.Presentation.Constants;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.MessageView.Presentation
{
    public sealed class MessageViewUtil : IMessageViewUtil, IAlertUtil
    {
        // NOTE: デフォルトで読み込まれるUIAsset以下のAlert対応されたプレハブ名を指定する
        const string DefaultUIAlertViewPrefabName = "MessageView";

        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public MessageViewController ShowMessageWithOk(
            string title,
            string message,
            string attentionMessage = null,
            Action onOk = null,
            bool enableEscape = true,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message, attentionMessage,  prefabName);

            var okAction = new UIMessageAction(
                Terms.Get("common_ok"),
                UIMessageActionStyle.Default,
                _ => onOk?.Invoke());
            controller.AddAction(okAction);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                enableEscape, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_001,
                () => onOk?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        public MessageViewController ShowMessageWithClose(
            string title,
            string message,
            string attentionMessage = null,
            Action onClose = null,
            bool enableEscape = true,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message, attentionMessage,  prefabName);
            var okAction = new UIMessageAction(
                Terms.Get("common_close"),
                UIMessageActionStyle.Cancel,
                _ => onClose?.Invoke());
            controller.AddAction(okAction);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                enableEscape, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_003,
                () => onClose?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        public MessageViewController ShowConfirmMessage(
            string title,
            string message,
            string attentionMessage = null,
            Action onOk = null,
            Action onCancel = null,
            bool enableEscape = true,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message, attentionMessage, prefabName);

            var cancelAction = new UIMessageAction(
                Terms.Get("common_cancel"),
                UIMessageActionStyle.Cancel,
                _ => onCancel?.Invoke());
            controller.AddAction(cancelAction);

            var okAction = new UIMessageAction(
                @Terms.Get("common_ok"),
                UIMessageActionStyle.Default,
                _ => onOk?.Invoke());
            controller.AddAction(okAction);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                true, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_003,
                () => onCancel?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        public MessageViewController ShowMessageWithButton(
            string title,
            string message,
            string attentionMessage,
            string buttonTitle,
            Action onOk = null,
            bool enableEscape = true,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message, attentionMessage, prefabName);

            var okAction = new UIMessageAction(
                buttonTitle,
                UIMessageActionStyle.Default,
                _ => onOk?.Invoke());
            controller.AddAction(okAction);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                enableEscape, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_001,
                () => onOk?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        public MessageViewController ShowMessageWith2Buttons(
            string title,
            string message,
            string attentionMessage,
            string option1ButtonTitle,
            string option2ButtonTitle,
            Action action1 = null,
            Action action2 = null,
            Action onClose = null,
            bool enableEscape = true,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message, attentionMessage, prefabName);

            var optionAction2 = new UIMessageAction(
                option2ButtonTitle,
                UIMessageActionStyle.Cancel,
                _ => action2?.Invoke());
            controller.AddAction(optionAction2);

            var optionAction1 = new UIMessageAction(
                option1ButtonTitle,
                UIMessageActionStyle.Default,
                _ => action1?.Invoke());
            controller.AddAction(optionAction1);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                enableEscape, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_003,
                () => onClose?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        public MessageViewController ShowMessageWith3Buttons(
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
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = MessageViewController.WithTitleAndMessage(title, message,attentionMessage, prefabName);

            var option3Action = new UIMessageAction(
                option3ButtonTitle,
                UIMessageActionStyle.Cancel,
                _ => onOption3?.Invoke());
            controller.AddAction(option3Action);

            var option2Action = new UIMessageAction(
                option2ButtonTitle,
                UIMessageActionStyle.Cancel,
                _ => onOption2?.Invoke());
            controller.AddAction(option2Action);

            var option1Action = new UIMessageAction(
                option1ButtonTitle,
                UIMessageActionStyle.Default,
                _ => onOption1?.Invoke());
            controller.AddAction(option1Action);

            Canvas.RootViewController.PresentModally(controller);

            var responder = new MessageViewEscapeResponder(
                controller, 
                enableEscape, 
                SystemSoundEffectProvider,
                SoundEffectId.SSE_000_003,
                () => onClose?.Invoke());
            EscapeResponderRegistry.Bind(responder, controller.View);

            return controller;
        }

        // 以降、WPFrameworkから呼ばれるとき用
        // GLOW側では基本使わない
        
        void IAlertUtil.ShowAlert(
            string title, 
            string message, 
            Action onOk = null, 
            bool enableEscape = true,
            bool enableOptionalDestructive = false, 
            string prefabName = null)
        {
            ShowMessageWithOk(
                title, 
                message, 
                null, 
                onOk, 
                enableEscape, 
                prefabName);
        }

        void IAlertUtil.ShowAlertWithOption(
            string title, 
            string message, 
            string buttonTitle, 
            Action onOk = null, 
            bool enableEscape = true,
            bool enableOptionalDestructive = false, 
            string prefabName = null)
        {
            ShowMessageWithButton(
                title, 
                message, 
                null, 
                buttonTitle, 
                onOk, 
                enableEscape, 
                prefabName);
        }

        void IAlertUtil.ShowAlertOkEscapeBind(
            string title, 
            string message, 
            Action onOk, 
            bool enableOptionalDestructive = false,
            string prefabName = null)
        {
            ShowMessageWithOk(
                title, 
                message, 
                null, 
                onOk, 
                true, 
                prefabName);
        }

        void IAlertUtil.ShowAlertConfirm(
            string title, 
            string message, 
            Action onOk, 
            Action onCancel = null,
            bool enableOptionalDestructive = false)
        {
            ShowConfirmMessage(
                title, 
                message, 
                null, 
                onOk, 
                onCancel, 
                true);
        }

        void IAlertUtil.ShowAlertConfirmWithOption(
            string title, 
            string message, 
            string option1ButtonTitle, 
            string option2ButtonTitle,
            Action onOption1, 
            Action onOption2, 
            bool enableEscape = true, 
            bool enableOptionalDestructive = false,
            string prefabName = null)
        {
            ShowMessageWith2Buttons(
                title, 
                message, 
                null, 
                option1ButtonTitle, 
                option2ButtonTitle, 
                onOption1, 
                onOption2, 
                onOption2, 
                enableEscape, 
                prefabName);
        }

        void IAlertUtil.ShowAlertConfirmWithOption(
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
            string prefabName = null)
        {
            ShowMessageWith3Buttons(
                title, 
                message, 
                null, 
                option1ButtonTitle, 
                option2ButtonTitle, 
                option3ButtonTitle, 
                onOption1, 
                onOption2, 
                onOption3, 
                onOption3, 
                enableEscape, 
                prefabName);
        }
    }
}
