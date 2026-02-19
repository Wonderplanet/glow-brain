using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainBadgeViewModel(
        NotificationBadge DailyMission,
        NotificationBadge EventMission,
        NotificationBadge BeginnerMission,
        NotificationBadge Encyclopedia,
        NotificationBadge IdleIncentive,
        NotificationBadge Announcement,
        NotificationBadge MessageBox);

}
