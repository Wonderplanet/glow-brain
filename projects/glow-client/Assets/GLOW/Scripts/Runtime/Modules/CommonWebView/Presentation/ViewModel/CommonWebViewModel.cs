using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.CommonWebView.Presentation.ViewModel
{
    public class CommonWebViewModel : ICommonWebViewModel
    {
        public string Title { get; }
        public string Url { get; }
        public HookedPatternUrl HookedPatternUrl { get; }

        public CommonWebViewModel(string title, string url, HookedPatternUrl hookedPatternUrl)
        {
            Title = title;
            Url = url;
            HookedPatternUrl = hookedPatternUrl;
        }
    }
}
