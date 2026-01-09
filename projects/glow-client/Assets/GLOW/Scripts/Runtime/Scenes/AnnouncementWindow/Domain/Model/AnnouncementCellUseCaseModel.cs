using System;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Model
{
    public record AnnouncementCellUseCaseModel(
        AnnouncementId AnnouncementId,
        AnnouncementTabType AnnouncementTabType,
        AnnouncementCellType AnnouncementCellType,
        AnnouncementCategory AnnouncementCategory,
        AnnouncementLastUpdateAt AnnouncementLastUpdateAt,
        AnnouncementTitle AnnouncementTitle,
        AnnouncementBannerUrl AnnouncementBannerUrl,
        AnnouncementStatus AnnouncementStatus,
        AnnouncementContentsUrl AnnouncementContentsUrl,
        AnnouncementStartAt AnnouncementStartAt,
        AnnouncementEndAt AnnouncementEndAt,
        bool IsRead)
    {
        public static AnnouncementCellUseCaseModel Empty { get; } = new(
            AnnouncementId.Empty,
            AnnouncementTabType.Event,
            AnnouncementCellType.Text,
            AnnouncementCategory.Other,
            AnnouncementLastUpdateAt.Empty,
            AnnouncementTitle.Empty,
            AnnouncementBannerUrl.Empty,
            AnnouncementStatus.OutOfTerm,
            AnnouncementContentsUrl.Empty,
            new AnnouncementStartAt(DateTimeOffset.MinValue),
            new AnnouncementEndAt(DateTimeOffset.MaxValue),
            false);
        
        public AnnouncementLastUpdateAt GetLastUpdateAt()
        {
            return AnnouncementLastUpdateAt.Max(
                AnnouncementLastUpdateAt,
                AnnouncementStartAt.ToLastUpdateAt());
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
