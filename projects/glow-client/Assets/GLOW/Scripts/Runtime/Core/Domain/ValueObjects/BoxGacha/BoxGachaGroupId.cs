using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.BoxGacha
{
    public record BoxGachaGroupId(ObscuredString Value)
    {
        public static BoxGachaGroupId Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}