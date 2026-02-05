using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// キャラや拠点にかかる効果の回数
    /// </summary>
    /// <param name="Value"></param>
    public record EffectiveCount(ObscuredInt Value)
    {
        public static EffectiveCount Empty { get; } = new(0);
        public static EffectiveCount Infinity { get; } = new(int.MaxValue);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static EffectiveCount operator +(EffectiveCount a, EffectiveCount b)
        {
            return new EffectiveCount(a.Value + b.Value);
        }

        public static EffectiveCount operator -(EffectiveCount a, EffectiveCount b)
        {
            return new EffectiveCount(a.Value - b.Value);
        }

        public static EffectiveCount operator +(EffectiveCount a, int b)
        {
            return new EffectiveCount(a.Value + b);
        }

        public static EffectiveCount operator -(EffectiveCount a, int b)
        {
            return new EffectiveCount(a.Value - b);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInfinity()
        {
            return ReferenceEquals(this, Infinity);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
