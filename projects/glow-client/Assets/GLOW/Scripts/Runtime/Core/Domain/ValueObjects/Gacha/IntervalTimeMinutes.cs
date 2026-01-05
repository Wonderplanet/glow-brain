using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record IntervalTimeMinutes(ObscuredInt Value)
    {
        public static IntervalTimeMinutes Empty { get; } = new IntervalTimeMinutes(0);
        public static IntervalTimeMinutes Zero { get; } = new IntervalTimeMinutes(0);

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
