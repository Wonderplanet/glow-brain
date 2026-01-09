using System;

namespace GLOW.Modules.InAppReview.Presentation
{
    public interface IInAppReviewWireFrame
    {
        void RequestStoreReview(Action onCompleted);
    }
}