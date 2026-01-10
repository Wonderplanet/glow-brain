using System;

namespace WPFramework.Presentation.Components
{
    public interface ICollectionCellAnimation
    {
        void AnimateCellAppear(float cellIntervalTime = 0.1f, float startDelaySeconds = 0f, Action onComplete = null);
        void AnimateCellDisappear();
    }
}
