using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// キャラや拠点にかかる効果の確率（%）
    /// </summary>
    /// <param name="Value"></param>
    public record EffectiveProbability(ObscuredInt Value)
    {
        public static EffectiveProbability Empty { get; } = new(0);
        public static EffectiveProbability Hundred { get; } = new(100);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static EffectiveProbability operator -(EffectiveProbability a, int b)
        {
            return new EffectiveProbability(a.Value - b);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsHundredOrMore()
        {
            return Value >= 100;
        }

        public bool IsZero()
        {
            return Value == 0;
        }

        public Percentage ToPercentage()
        {
            return new Percentage(Value);
        }
    }
}
