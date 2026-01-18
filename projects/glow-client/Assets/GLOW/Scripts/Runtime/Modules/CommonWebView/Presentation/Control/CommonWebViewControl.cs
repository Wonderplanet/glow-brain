using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Modules.CommonWebView.Domain.Merger;
using GLOW.Modules.CommonWebView.Presentation.View;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Modules.CommonWebView.Presentation.Control
{
    public class CommonWebViewControl : ICommonWebViewControl
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }

        [Inject] IViewFactory ViewFactory { get; }

        void ICommonWebViewControl.ShowWebView(WebViewShownContentType type)
        {
            var argument = new CommonWebViewController.Argument(type, AnnouncementContentsUrl.Empty, HookedPatternUrl.Empty);
            var controller =
                ViewFactory.Create<CommonWebViewController, CommonWebViewController.Argument>(argument);
            Canvas.RootViewController.PresentModally(controller);
        }

        void ICommonWebViewControl.ShowWebView(WebViewShownContentType type, Action onClose)
        {
            var argument = new CommonWebViewController.Argument(type, AnnouncementContentsUrl.Empty, HookedPatternUrl.Empty);
            var controller =
                ViewFactory.Create<CommonWebViewController, CommonWebViewController.Argument>(argument);
            controller.OnClose = onClose;

            Canvas.RootViewController.PresentModally(controller);
        }

        public void ShowAnnouncementWebView(
            AnnouncementContentsUrl announcementUrl,
            HookedPatternUrl hookedPatternUrlInAnnouncements)
        {
            // ユーザーアンケートのURLをフックするために正規表現にする
            var hookedPatternUrlUserQuestion = new HookedPatternUrl(Regex.Escape(Credentials.UserQuestionnaireURL));
            var hookedPatternUrlList = new List<HookedPatternUrl> { hookedPatternUrlInAnnouncements, hookedPatternUrlUserQuestion };
            var hookedPatternUrl = HookedPatternUrlMerger.MergeAsOrPattern(hookedPatternUrlList);
            
            var argument = new CommonWebViewController.Argument(WebViewShownContentType.Announcement, announcementUrl, hookedPatternUrl);
            var controller =
                ViewFactory.Create<CommonWebViewController, CommonWebViewController.Argument>(argument);

            Canvas.RootViewController.PresentModally(controller);
        }
    }
}
