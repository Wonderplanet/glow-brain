using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainBadgeModel(
        NotificationBadge DailyMission,
        NotificationBadge EventMission,
        NotificationBadge BeginnerMission,
        NotificationBadge Encyclopedia,
        NotificationBadge IdleIncentive,
        NotificationBadge Announcement,
        NotificationBadge MessageBox)
    {
        public static HomeMainBadgeModel Empty { get; } = new HomeMainBadgeModel(
            NotificationBadge.False,
            NotificationBadge.False,
            NotificationBadge.False,
            NotificationBadge.False,
            NotificationBadge.False,
            NotificationBadge.False,
            NotificationBadge.False);
    }
}
