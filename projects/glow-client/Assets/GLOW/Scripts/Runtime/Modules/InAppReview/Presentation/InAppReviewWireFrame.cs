using System;
using WonderPlanet.InAppReview;

namespace GLOW.Modules.InAppReview.Presentation
{
    public class InAppReviewWireFrame : IInAppReviewWireFrame
    {
        public void RequestStoreReview(Action onCompleted)
        {
            IStoreReviewRequest reviewRequest = new StoreReviewRequest();
            reviewRequest.RequestStoreReview(onCompleted);
        }
    }
}