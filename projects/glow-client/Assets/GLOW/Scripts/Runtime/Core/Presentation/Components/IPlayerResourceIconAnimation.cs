using System;

namespace GLOW.Core.Presentation.Components
{
    public interface IPlayerResourceIconAnimation : IDisposable
    {
        void ScrollAnimation(int viewModelCount, int startScrollRow, Action onComplete);
        void SkipOneFrame();
        void CellAnimation(IPlayerResourceIconAnimationCell cell, int index, int viewModelCount);
        void SkipAnimation();
    }
}
