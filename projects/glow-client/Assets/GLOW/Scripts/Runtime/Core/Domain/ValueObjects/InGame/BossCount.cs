using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record BossCount(ObscuredInt Value)
    {
        public static BossCount Empty { get; } = new(0);
        public static BossCount Infinity { get; } = new(int.MaxValue);

        public static bool operator <(BossCount a, int b)
        {
            return a.Value < b;
        }

        public static bool operator <=(BossCount a, int b)
        {
            return a.Value <= b;
        }

        public static bool operator >(BossCount a, int b)
        {
            return a.Value > b;
        }

        public static bool operator >=(BossCount a, int b)
        {
            return a.Value >= b;
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