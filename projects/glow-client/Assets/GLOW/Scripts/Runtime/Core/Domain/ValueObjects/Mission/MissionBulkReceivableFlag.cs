namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionBulkReceivableFlag(bool Value)
    {
        public static MissionBulkReceivableFlag True { get; } = new MissionBulkReceivableFlag(true);
        public static MissionBulkReceivableFlag False { get; } = new MissionBulkReceivableFlag(false);

        public static implicit operator bool(MissionBulkReceivableFlag flag) => flag.Value;

        public NotificationBadge ToNotificationBadge() => new NotificationBadge(Value);
    }
}