using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SpecialRuleUnitStatusEffectValue(ObscuredInt Value)
    {
        public static SpecialRuleUnitStatusEffectValue Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }

        public TickCount ToTickCount()
        {
            return new TickCount((long)Value);
        }
    }
}
