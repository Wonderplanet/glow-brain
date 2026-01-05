using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitEncyclopediaEffectValue(ObscuredDecimal Value)
    {
        public static UnitEncyclopediaEffectValue Empty { get; } = new (0);

        public override string ToString()
        {
            return Value.ToString();
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }

        public static bool operator == (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value == b;
        }

        public static bool operator != (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value != b;
        }

        public static bool operator < (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value < b;
        }

        public static bool operator > (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value > b;
        }

        public static bool operator <= (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >= (UnitEncyclopediaEffectValue a, int b)
        {
            return a.Value >= b;
        }

        public static UnitEncyclopediaEffectValue operator + (UnitEncyclopediaEffectValue a, UnitEncyclopediaEffectValue b)
        {
            return new UnitEncyclopediaEffectValue(a.Value + b.Value);
        }

        public static UnitEncyclopediaEffectValue operator - (UnitEncyclopediaEffectValue a, UnitEncyclopediaEffectValue b)
        {
            return new UnitEncyclopediaEffectValue(a.Value - b.Value);
        }
    }
}
