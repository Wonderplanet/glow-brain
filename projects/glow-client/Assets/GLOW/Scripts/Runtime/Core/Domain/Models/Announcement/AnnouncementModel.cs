using System;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Core.Domain.Models.Announcement
{
    public record AnnouncementModel(
        AnnouncementId AnnouncementId,
        AnnouncementOsType AnnouncementOsType,
        AnnouncementLastUpdateAt LastUpdatedAt,
        AnnouncementCreatedAt CreatedAt,
        AnnouncementContentsUrl ContentsUrl,
        AnnouncementTitle Title,
        AnnouncementBannerUrl BannerUrl,
        AnnouncementCategory AnnouncementCategory,
        AnnouncementStatus Status,
        AnnouncementStartAt StartAt,
        AnnouncementEndAt EndAt)
    {
        public static AnnouncementModel Empty { get; } = new(
            AnnouncementId.Empty,
            AnnouncementOsType.Empty,
            AnnouncementLastUpdateAt.Empty,
            new AnnouncementCreatedAt(DateTimeOffset.MinValue),
            AnnouncementContentsUrl.Empty,
            AnnouncementTitle.Empty,
            AnnouncementBannerUrl.Empty,
            AnnouncementCategory.Other,
            AnnouncementStatus.OutOfTerm,
            new AnnouncementStartAt(DateTimeOffset.MinValue),
            new AnnouncementEndAt(DateTimeOffset.MaxValue));
        
        public AnnouncementLastUpdateAt GetLastUpdateAt()
        {
            return AnnouncementLastUpdateAt.Max(
                LastUpdatedAt,
                StartAt.ToLastUpdateAt());
        }
    }
}
