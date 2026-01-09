using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpHeldNumber(ObscuredInt Value)
    {
        public static PvpHeldNumber Empty { get; } = new PvpHeldNumber(0);
        public static PvpHeldNumber Zero { get; } = new PvpHeldNumber(0);

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
