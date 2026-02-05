using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Modules.CommonWebView.Presentation.Control
{
    public interface ICommonWebViewControl
    {
        void ShowWebView(WebViewShownContentType type);
        void ShowWebView(WebViewShownContentType type, Action onClose);

        void ShowAnnouncementWebView(
            AnnouncementContentsUrl announcementUrl,
            HookedPatternUrl hookedPatternUrlInAnnouncements);
    }
}
