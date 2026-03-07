using GLOW.Core.Domain.Resolvers;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Modules.CommonWebView.Presentation.Constants;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views.Components;
using UIKit;
using UIKit.WebView;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Presentation.Views
{
    public class GachaDetailAnnouncementWebViewController :
        UIViewController<GachaDetailAnnouncementWebView>,
        IUIWebViewControllerDelegate
    {
        [Inject] IWebCdnHostResolver WebCdnHostResolver { get; }

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
        }

        public void LoadURL(AnnouncementContentsUrl contentsUrl)
        {
            var baseUrl = WebCdnHostResolver.Resolve().Uri;
            var webViewUrl = $"{baseUrl}/{contentsUrl.Value}";

            _webViewController.LoadURL(webViewUrl);
        }

        void IUIWebViewControllerDelegate.OnWebViewCallBack(string msg)
        {

        }

        void IUIWebViewControllerDelegate.OnWebViewHooked(string msg)
        {

        }

        void IUIWebViewControllerDelegate.OnWebViewError(string msg)
        {

        }
    }
}
