using System;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Extensions;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public sealed class AlertUtil : IAlertUtil
    {
        // NOTE: デフォルトで読み込まれるUIAsset以下のAlert対応されたプレハブ名を指定する
        const string DefaultUIAlertViewPrefabName = "UIAlertView";

        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public void ShowAlert(
            string title,
            string message,
            Action onOk = null,
            bool enableEscape = false,
            bool enableOptionalDestructive = false,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = UIAlertController.WithTitleAndMessage(title, message, prefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var okAction = new UIAlertAction(
                Terms.Get("common_ok"),
                UIAlertActionStyle.Default,
                _ => onOk?.Invoke());

            controller.AddAction(okAction);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, enableEscape, SystemSoundEffectProvider);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        public void ShowAlertWithOption(
            string title,
            string message,
            string buttonTitle,
            Action onOk = null,
            bool enableEscape = true,
            bool enableOptionalDestructive = false,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = UIAlertController.WithTitleAndMessage(title, message, prefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var buttonAction = new UIAlertAction(
                buttonTitle,
                UIAlertActionStyle.Default,
                _ => onOk?.Invoke());

            controller.AddAction(buttonAction);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, enableEscape, SystemSoundEffectProvider);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        public void ShowAlertOkEscapeBind(
            string title,
            string message,
            Action onOk,
            bool enableOptionalDestructive = false,
            string prefabName = null)
        {
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = UIAlertController.WithTitleAndMessage(title, message, prefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var okAction = new UIAlertAction(
                Terms.Get("common_ok"),
                UIAlertActionStyle.Default,
                _ => onOk?.Invoke());

            controller.AddAction(okAction);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, true, SystemSoundEffectProvider, okAction);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        public void ShowAlertConfirm(
            string title,
            string message,
            Action onOk,
            Action onCancel = null,
            bool enableOptionalDestructive = false)
        {
            var controller = UIAlertController.WithTitleAndMessage(title, message, DefaultUIAlertViewPrefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var okAction = new UIAlertAction(
                @Terms.Get("common_ok"),
                UIAlertActionStyle.Default,
                _ => onOk?.Invoke());
            var cancelAction = new UIAlertAction(
                Terms.Get("common_cancel"),
                UIAlertActionStyle.Cancel,
                _ => onCancel?.Invoke());
            controller.AddAction(cancelAction);
            controller.AddAction(okAction);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, true, SystemSoundEffectProvider, cancelAction);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        public void ShowAlertConfirmWithOption(
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
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = UIAlertController.WithTitleAndMessage(title, message, prefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var option1Action = new UIAlertAction(
                option1ButtonTitle,
                UIAlertActionStyle.Default,
                _ => onOption1?.Invoke());
            var option2Action = new UIAlertAction(
                option2ButtonTitle,
                UIAlertActionStyle.Cancel,
                _ => onOption2?.Invoke());
            controller.AddAction(option2Action);
            controller.AddAction(option1Action);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, enableEscape, SystemSoundEffectProvider, option2Action);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        public void ShowAlertConfirmWithOption(
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
            if (string.IsNullOrEmpty(prefabName))
            {
                prefabName = DefaultUIAlertViewPrefabName;
            }

            var controller = UIAlertController.WithTitleAndMessage(title, message, prefabName);
            if (string.IsNullOrEmpty(title))
            {
                HideAlertViewTitle(controller);
            }

            var option1Action = new UIAlertAction(
                option1ButtonTitle,
                UIAlertActionStyle.Default,
                _ => onOption1?.Invoke());
            var option2Action = new UIAlertAction(
                option2ButtonTitle,
                UIAlertActionStyle.Cancel,
                _ => onOption2?.Invoke());
            var option3Action = new UIAlertAction(
                option3ButtonTitle,
                UIAlertActionStyle.Destructive,
                _ => onOption3?.Invoke());
            controller.AddAction(option3Action);
            controller.AddAction(option2Action);
            controller.AddAction(option1Action);
            Canvas.RootViewController.PresentModally(controller);

            var responder = new AlertEscapeResponder(controller, enableEscape, SystemSoundEffectProvider, option3Action);
            EscapeResponderRegistry.Bind(responder, controller.View);
        }

        void HideAlertViewTitle(UIAlertController controller)
        {
            controller.ActualView.TitleText.transform.parent.gameObject.SetActive(false);
        }
    }
}
