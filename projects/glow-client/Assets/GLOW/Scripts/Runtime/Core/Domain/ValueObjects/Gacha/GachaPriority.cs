using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaPriority(ObscuredInt Value)
    {
        public static GachaPriority Empty { get; } = new(-1);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static bool operator <(GachaPriority a, GachaPriority b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(GachaPriority a, GachaPriority b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(GachaPriority a, GachaPriority b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(GachaPriority a, GachaPriority b)
        {
            return a.Value >= b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
