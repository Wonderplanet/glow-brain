using System;

namespace WPFramework.Presentation.Components
{
    public interface IUIViewTransition
    {
        void Play(Action completion = null);
        bool IsSourceViewDisappeared { get; }
        bool IsDestinationViewAppeared { get; }
    }
}
