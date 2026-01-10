namespace GLOW.Core.Domain.ValueObjects
{
    public record StaminaBoostFlag(bool Value)
    {
        public static StaminaBoostFlag True { get; } = new StaminaBoostFlag(true);
        public static StaminaBoostFlag False { get; } = new StaminaBoostFlag(false);

        public static implicit operator bool(StaminaBoostFlag staminaBoostFlag) => staminaBoostFlag.Value;
    }
}
