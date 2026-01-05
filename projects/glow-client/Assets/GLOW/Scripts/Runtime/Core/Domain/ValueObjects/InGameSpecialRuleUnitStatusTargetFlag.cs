namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameSpecialRuleUnitStatusTargetFlag(bool Value)
    {
        public static InGameSpecialRuleUnitStatusTargetFlag True { get; } = new InGameSpecialRuleUnitStatusTargetFlag(true);
        public static InGameSpecialRuleUnitStatusTargetFlag False { get; } = new InGameSpecialRuleUnitStatusTargetFlag(false);

        public static implicit operator bool(InGameSpecialRuleUnitStatusTargetFlag flag) => flag.Value;
    }
}