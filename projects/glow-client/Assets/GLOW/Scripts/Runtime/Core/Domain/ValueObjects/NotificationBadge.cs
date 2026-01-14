namespace GLOW.Core.Domain.ValueObjects
{
    public record NotificationBadge(bool Value)
    {
        public static NotificationBadge False { get; } = new(false);
        public static NotificationBadge True { get; } = new(true);

        public static implicit operator bool(NotificationBadge flag) => flag.Value;
        public static NotificationBadge operator !(NotificationBadge flag) => new(!flag.Value);
    }
}
