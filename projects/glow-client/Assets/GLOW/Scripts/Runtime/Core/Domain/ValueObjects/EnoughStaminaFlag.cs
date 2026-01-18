namespace GLOW.Core.Domain.ValueObjects
{
    public record EnoughStaminaFlag(bool Value)
    {
        public static EnoughStaminaFlag True { get; } = new EnoughStaminaFlag(true);
        public static EnoughStaminaFlag False { get; } = new EnoughStaminaFlag(false);

        public static implicit operator bool(EnoughStaminaFlag enoughStaminaFlag) => enoughStaminaFlag.Value;
    }
}