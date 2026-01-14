namespace GLOW.Core.Domain.ValueObjects
{
    public record ExistsSpecialRuleFlag(bool Value)
    {
        public static ExistsSpecialRuleFlag True { get; } = new ExistsSpecialRuleFlag(true);
        public static ExistsSpecialRuleFlag False { get; } = new ExistsSpecialRuleFlag(false);

        public static implicit operator bool(ExistsSpecialRuleFlag flag) => flag.Value;
    }
}
