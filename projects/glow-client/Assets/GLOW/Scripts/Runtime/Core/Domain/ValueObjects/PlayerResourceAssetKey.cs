using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceAssetKey(ObscuredString Value)
    {
        public static PlayerResourceAssetKey Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
