using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.CommonWebView.Presentation.Constants;
using UIKit;
using UIKit.WebView;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AgreementDialog.Presentation.Views
{
    public class AgreementDialogViewController : UIViewController<AgreementDialogView>, IUIWebViewControllerDelegate, IEscapeResponder
    {
        [Serializable]
        public record Argument(Action Complete, AgreementUrl Url)
        {
            public AgreementUrl Url { get; } = Url;
            public Action Complete { get; } = Complete;
        }

        [Inject] IAgreementDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] Argument Argc { get; set; }

        UIWebViewController _webViewController;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

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
            ViewDelegate.OnWebViewError();
        }

        public void SetUrl(AgreementUrl url, string hookedPatternUrl)
        {
            _webViewController.SetUrlPattern(
                hookedPatternUrl: hookedPatternUrl);
            _webViewController.LoadURL(url.Value);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }

        public void Close()
        {
            Dismiss();
            Argc.Complete?.Invoke();
        }
    }
}
