using System;

namespace GLOW.Core.Presentation.Components
{
    public interface ICollectionCellAnimation : IDisposable
    {
        void AnimateCellInOrderAppear(float cellIntervalTime = 0.1f, float startDelaySeconds = 0f, int startScrollRow = 1, Action onComplete = null, bool scrollStartDelayTime = true);

        void SkipAnimation();
    }
}
