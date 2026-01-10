using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IQuestStageReleaseAnimationRepository
    {
        void SaveForHomeTop(ShowReleaseAnimationStatus status);
        ShowReleaseAnimationStatus GetForHomeTop();
        void SaveForEventStageSelect(ShowReleaseAnimationStatus status);
        ShowReleaseAnimationStatus GetForEventStageSelect();
        void DeleteAtNormal();
        void DeleteAtEvent();
    }
}
