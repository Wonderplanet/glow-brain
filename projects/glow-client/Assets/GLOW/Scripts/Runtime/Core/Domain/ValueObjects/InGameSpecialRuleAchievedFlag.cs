namespace GLOW.Core.Domain.ValueObjects
{
    public record InGameSpecialRuleAchievedFlag(bool Value)
    {
        public static InGameSpecialRuleAchievedFlag True { get; } = new InGameSpecialRuleAchievedFlag(true);
        public static InGameSpecialRuleAchievedFlag False { get; } = new InGameSpecialRuleAchievedFlag(false);

        public static implicit operator bool(InGameSpecialRuleAchievedFlag flag) => flag.Value;
    }
}
