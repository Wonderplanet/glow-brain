using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel
{
    public record AnnouncementCellViewModel(
        AnnouncementId AnnouncementId,
        AnnouncementCellType InformationCellType,
        AnnouncementCategory AnnouncementCategory,
        AnnouncementStartAt AnnouncementStartAt,
        AnnouncementTitle AnnouncementTitle,
        AnnouncementBannerUrl AnnouncementBannerUrl,
        AnnouncementStatus AnnouncementStatus,
        AnnouncementContentsUrl AnnouncementContentsUrl,
        bool IsRead);
}