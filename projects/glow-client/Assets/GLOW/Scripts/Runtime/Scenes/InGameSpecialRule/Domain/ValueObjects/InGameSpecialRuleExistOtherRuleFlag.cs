namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleExistOtherRuleFlag(bool Value)
    {
        public static InGameSpecialRuleExistOtherRuleFlag True { get; } = new (true);
        public static InGameSpecialRuleExistOtherRuleFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleExistOtherRuleFlag flag) => flag.Value;
    }
}
