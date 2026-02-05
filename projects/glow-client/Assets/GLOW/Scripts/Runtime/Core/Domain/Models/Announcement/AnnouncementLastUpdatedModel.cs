using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Core.Domain.Models.Announcement
{
    public record AnnouncementLastUpdatedModel(
        AnnouncementLastUpdateAt InformationIos,
        AnnouncementLastUpdateAt OperationIos,
        AnnouncementLastUpdateAt InformationAndroid,
        AnnouncementLastUpdateAt OperationAndroid)
    {
        public static AnnouncementLastUpdatedModel Empty { get; } = new(
            AnnouncementLastUpdateAt.Empty,
            AnnouncementLastUpdateAt.Empty,
            AnnouncementLastUpdateAt.Empty,
            AnnouncementLastUpdateAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}