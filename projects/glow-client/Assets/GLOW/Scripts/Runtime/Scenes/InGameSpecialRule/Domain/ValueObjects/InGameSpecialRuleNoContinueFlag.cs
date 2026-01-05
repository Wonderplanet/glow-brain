namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleNoContinueFlag(bool Value)
    {
        public static InGameSpecialRuleNoContinueFlag True { get; } = new InGameSpecialRuleNoContinueFlag(true);
        public static InGameSpecialRuleNoContinueFlag False { get; } = new InGameSpecialRuleNoContinueFlag(false);

        public static implicit operator bool(InGameSpecialRuleNoContinueFlag flag) => flag.Value;
    }
}