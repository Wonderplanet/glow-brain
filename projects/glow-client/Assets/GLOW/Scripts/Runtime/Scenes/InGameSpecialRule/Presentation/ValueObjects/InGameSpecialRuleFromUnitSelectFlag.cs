namespace GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects
{
    public record InGameSpecialRuleFromUnitSelectFlag(bool Value)
    {
        public static InGameSpecialRuleFromUnitSelectFlag True { get; } = new InGameSpecialRuleFromUnitSelectFlag(true);
        public static InGameSpecialRuleFromUnitSelectFlag False { get; } = new InGameSpecialRuleFromUnitSelectFlag(false);

        public static implicit operator bool(InGameSpecialRuleFromUnitSelectFlag flag) => flag.Value;
    }
}