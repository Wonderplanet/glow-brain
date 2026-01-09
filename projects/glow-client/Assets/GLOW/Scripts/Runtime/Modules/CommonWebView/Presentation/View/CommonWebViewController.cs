using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonWebView.Presentation.Constants;
using GLOW.Modules.CommonWebView.Presentation.ViewModel;
using UIKit;
using UIKit.WebView;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.CommonWebView.Presentation.View
{
    public class CommonWebViewController : UIViewController<CommonWebView>, IUIWebViewControllerDelegate, IEscapeResponder
    {
        [Inject] ICommonWebViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public record Argument(WebViewShownContentType Type, AnnouncementContentsUrl AnnouncementUrl, HookedPatternUrl HookedPatternUrl);

        UIWebViewController _webViewController;
        public Action OnClose { get; set; }

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
            ViewDelegate.OnWebViewCallBack(msg);
        }

        public void OnWebViewHooked(string msg)
        {
            ViewDelegate.OnWebViewHooked(msg);
        }

        public void OnWebViewError(string msg)
        {
        }

        public void SetViewModel(ICommonWebViewModel viewModel)
        {
            if (!viewModel.HookedPatternUrl.IsEmpty())
            {
                _webViewController.SetUrlPattern(viewModel.HookedPatternUrl.Value);
            }

            _webViewController.LoadURL(viewModel.Url);
            ActualView.TitleText.SetText(viewModel.Title);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }

        [UIAction]
        void OnClosed()
        {
            Close();
        }

        void Close()
        {
            OnClose?.Invoke();
            Dismiss();
        }

    }
}
