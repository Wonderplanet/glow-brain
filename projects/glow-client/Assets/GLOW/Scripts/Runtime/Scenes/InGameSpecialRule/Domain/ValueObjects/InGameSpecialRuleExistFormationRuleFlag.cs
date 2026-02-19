namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleExistFormationRuleFlag(bool Value)
    {
        public static InGameSpecialRuleExistFormationRuleFlag True { get; } = new (true);
        public static InGameSpecialRuleExistFormationRuleFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleExistFormationRuleFlag flag) => flag.Value;
    }
}