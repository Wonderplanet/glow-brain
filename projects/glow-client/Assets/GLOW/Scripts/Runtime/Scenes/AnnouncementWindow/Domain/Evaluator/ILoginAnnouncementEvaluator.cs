using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Evaluator
{
    public interface ILoginAnnouncementEvaluator
    {
        bool ShouldShowLoginAnnouncement(
            AnnouncementLastUpdateAt informationLastUpdatedAt,
            AnnouncementLastUpdateAt operationLastUpdatedAt);
    }
}