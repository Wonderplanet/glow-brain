namespace GLOW.Core.Domain.ValueObjects
{
    public record ExistsPartySpecialRuleFlag(bool Value)
    {
        public static ExistsPartySpecialRuleFlag True { get; } = new (true);
        public static ExistsPartySpecialRuleFlag False { get; } = new (false);

        public static implicit operator bool(ExistsPartySpecialRuleFlag flag) => flag.Value;
    }
}