namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record HeldAdditionalStaminaPassEffectFlag(bool Value)
    {
        public static HeldAdditionalStaminaPassEffectFlag True { get; } = new (true);
        public static HeldAdditionalStaminaPassEffectFlag False { get; } = new (false);

        public static implicit operator bool(HeldAdditionalStaminaPassEffectFlag flag) => flag.Value;
    }
}