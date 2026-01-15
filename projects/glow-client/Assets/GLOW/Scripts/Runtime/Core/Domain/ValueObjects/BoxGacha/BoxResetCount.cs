using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.BoxGacha
{
    public record BoxResetCount(ObscuredInt Value)
    {
        public static BoxResetCount Empty { get; } = new(0);

        public int ToCurrentBoxNumber()
        {
            return Value + 1;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
