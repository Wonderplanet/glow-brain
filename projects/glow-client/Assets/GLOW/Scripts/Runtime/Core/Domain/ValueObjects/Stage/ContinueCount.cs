using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record ContinueCount(ObscuredInt Value)
    {
        public static ContinueCount Zero { get; } = new(0);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static bool operator <(ContinueCount a, ContinueCount b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(ContinueCount a, ContinueCount b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(ContinueCount a, ContinueCount b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(ContinueCount a, ContinueCount b)
        {
            return a.Value >= b.Value;
        }

        public static ContinueCount operator +(ContinueCount a, ContinueCount b)
        {
            return new ContinueCount(a.Value + b.Value);
        }

        public static ContinueCount operator -(ContinueCount a, ContinueCount b)
        {
            return new ContinueCount(a.Value - b.Value);
        }
    }
}
