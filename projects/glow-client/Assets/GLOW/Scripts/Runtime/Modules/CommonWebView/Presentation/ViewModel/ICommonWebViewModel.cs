using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.CommonWebView.Presentation.ViewModel
{
    public interface ICommonWebViewModel
    {
        public string Title { get; }

        public string Url { get; }
        public HookedPatternUrl HookedPatternUrl { get; }
    }
}
