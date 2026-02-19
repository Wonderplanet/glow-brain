using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AnnouncementWindow.Domain.ValueObject
{
    public record AlreadyReadAnnouncementFlag(bool Value)
    {
        public static AlreadyReadAnnouncementFlag False { get; }  = new(false);
        public static AlreadyReadAnnouncementFlag True { get; } = new(true);

        public static implicit operator bool(AlreadyReadAnnouncementFlag flag) => flag.Value;
        public static AlreadyReadAnnouncementFlag operator !(AlreadyReadAnnouncementFlag flag) => new(!flag.Value);

        public NotificationBadge ToNotificationBadge()
        {
            // True -> 表示しない(既読)
            // False -> 表示する(未読)
            return new NotificationBadge(!Value);
        }
    }
}