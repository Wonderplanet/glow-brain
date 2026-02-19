using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonWebView.Presentation.Constants;
using UIKit;
using UIKit.WebView;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PrivacyOptionDialog.Presentation.Views
{
    public class PrivacyOptionDialogViewController :
        UIViewController<PrivacyOptionDialogView>,
        IUIWebViewControllerDelegate,
        IEscapeResponder
    {
        public record Argument(AgreementUrl Url);

        [Inject] IPrivacyOptionDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        UIWebViewController _webViewController;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            _webViewController = new UIWebViewController
            {
                ViewControllerDelegate = this,
                PrefabName = "DefaultWebView"
            };
            // PlayerSettingsの設定されているTargetDpiは取得できないので、Constantsから取得
            _webViewController.SetTargetDpi(WebViewFixedDpi.TargetDpi);

            AddChild(_webViewController);
            _webViewController.View.transform.SetParent(ActualView.ContentTransform, false);

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        void IUIWebViewControllerDelegate.OnWebViewCallBack(string msg)
        {
        }

        void IUIWebViewControllerDelegate.OnWebViewHooked(string msg)
        {
            ViewDelegate.OnWebViewHooked(msg);
        }
        void IUIWebViewControllerDelegate.OnWebViewError(string msg)
        {
            ViewDelegate.OnWebViewError(msg);
        }

        public void SetUrl(AgreementUrl url, string hookedPatternUrl)
        {
            _webViewController.SetUrlPattern(
                hookedPatternUrl: hookedPatternUrl);
            _webViewController.LoadURL(url.Value);
        }

        public void Close()
        {
            Dismiss();
        }

        [UIAction]
        void OnClosed()
        {
            Close();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            UISoundEffector.Main.PlaySeEscape();
            Close();
            return true;
        }
    }
}
