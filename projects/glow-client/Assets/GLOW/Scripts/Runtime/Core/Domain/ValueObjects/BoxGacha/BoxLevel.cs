using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.BoxGacha
{
    public record BoxLevel(ObscuredInt Value)
    {
        public static BoxLevel Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}