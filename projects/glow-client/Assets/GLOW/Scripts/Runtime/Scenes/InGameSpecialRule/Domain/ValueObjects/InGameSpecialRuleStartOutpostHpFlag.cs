namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleStartOutpostHpFlag(bool Value)
    {
        public static InGameSpecialRuleStartOutpostHpFlag True { get; } = new InGameSpecialRuleStartOutpostHpFlag(true);
        public static InGameSpecialRuleStartOutpostHpFlag False { get; } = new InGameSpecialRuleStartOutpostHpFlag(false);

        public static implicit operator bool(InGameSpecialRuleStartOutpostHpFlag flag) => flag.Value;
    }
}