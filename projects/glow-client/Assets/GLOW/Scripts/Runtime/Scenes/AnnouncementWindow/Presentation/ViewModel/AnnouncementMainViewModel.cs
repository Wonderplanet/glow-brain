using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel
{
    public record AnnouncementMainViewModel(
        AnnouncementEventViewModel AnnouncementEventViewModel,
        AnnouncementOperationViewModel AnnouncementOperationViewModel,
        HookedPatternUrl HookedPatternUrlInAnnouncements);
}