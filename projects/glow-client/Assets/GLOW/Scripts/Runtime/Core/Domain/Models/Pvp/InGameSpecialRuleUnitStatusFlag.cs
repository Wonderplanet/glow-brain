using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record InGameSpecialRuleUnitStatusFlag(bool Value)
    {
        public static InGameSpecialRuleUnitStatusFlag True { get; } = new InGameSpecialRuleUnitStatusFlag(true);
        public static InGameSpecialRuleUnitStatusFlag False { get; } = new InGameSpecialRuleUnitStatusFlag(false);

        public static implicit operator bool(InGameSpecialRuleUnitStatusFlag flag) => flag.Value;
    }
}