namespace GLOW.Core.Domain.ValueObjects.StaminaRecover
{
    public record StaminaRecoveryFlag(bool Value)
    {
        public static StaminaRecoveryFlag True { get; } = new (true);
        public static StaminaRecoveryFlag False { get; } = new (false);
        
        public static implicit operator bool(StaminaRecoveryFlag flag) => flag.Value;

        public NotificationBadge ToNotificationBadge()
        {
            return new NotificationBadge(Value);
        }
    }
}
