using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonWebView.Presentation.Constants;
using UIKit;
using UIKit.WebView;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views
{
    public class LinkBnIdWebViewDialogViewController : UIViewController<LinkBnIdWebViewDialogView>, IUIWebViewControllerDelegate, IEscapeResponder
    {
        [Inject] ILinkBnIdWebViewDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        UIWebViewController _webViewController;

        public Action<BnIdCode> OnRedirected { get; set; }

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

        public void OnWebViewCallBack(string msg)
        {

        }

        public void OnWebViewHooked(string msg)
        {
            ViewDelegate.OnWebViewHooked(msg);
        }

        public void OnWebViewError(string msg)
        {

        }

        public void LoadURL(string url)
        {
            _webViewController.ClearCookies();
            _webViewController.SetUrlPattern(
                hookedPatternUrl: "^jumble-rush://\\?code=.*");
            _webViewController.IsSelectStyleEnabled = false;
            _webViewController.LoadURL(url);
        }

        public void Close()
        {
            Dismiss();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            Close();
            return true;
        }

        [UIAction]
        void OnClosed()
        {
            Close();
        }

        [UIAction]
        void OnForward()
        {
            _webViewController.GoForward();
        }

        [UIAction]
        void OnPrevious()
        {
            _webViewController.GoBack();
        }
    }
}
