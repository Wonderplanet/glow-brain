namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleSpeedAttackFlag(bool Value)
    {
        public static InGameSpecialRuleSpeedAttackFlag True { get; } = new (true);
        public static InGameSpecialRuleSpeedAttackFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleSpeedAttackFlag flag) => flag.Value;
    }
}