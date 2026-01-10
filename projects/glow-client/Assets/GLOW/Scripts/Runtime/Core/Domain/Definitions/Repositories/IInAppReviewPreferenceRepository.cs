using GLOW.Modules.InAppReview.Domain.ValueObject;

namespace GLOW.Core.Domain.Repositories
{
    public interface IInAppReviewPreferenceRepository
    {
        InAppReviewFlag IsAppReviewDisplayedAfterGachaUrDrawn { get; }
        void SetIsAppReviewDisplayedAfterGachaUrDrawn(InAppReviewFlag flag);
    }
}