using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    /// <summary>
    /// ガチャを引いた回数
    /// </summary>
    public record GachaPlayedCount(ObscuredInt Value) : IGachaCountableValueObject
    {
        public static GachaPlayedCount Zero { get; } = new(0);

        public static bool operator <(GachaPlayedCount a, GachaPlayedCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(GachaPlayedCount a, GachaPlayedCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(GachaPlayedCount a, GachaPlayedCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(GachaPlayedCount a, GachaPlayedCount b)
        {
            return a.Value >= b.Value;
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
