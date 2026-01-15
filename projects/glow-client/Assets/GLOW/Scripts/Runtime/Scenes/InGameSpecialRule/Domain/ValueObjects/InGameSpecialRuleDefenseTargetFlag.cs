namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleDefenseTargetFlag(bool Value)
    {
        public static InGameSpecialRuleDefenseTargetFlag True { get; } = new InGameSpecialRuleDefenseTargetFlag(true);
        public static InGameSpecialRuleDefenseTargetFlag False { get; } = new InGameSpecialRuleDefenseTargetFlag(false);

        public static implicit operator bool(InGameSpecialRuleDefenseTargetFlag flag) => flag.Value;
    }
}