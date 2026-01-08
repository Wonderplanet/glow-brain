using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkAssetKey(ObscuredString Value)
    {
        public static ArtworkAssetKey Empty { get; } = new (string.Empty);
        public static ArtworkAssetKey Default { get; } = new ("artwork_tutorial_0001a");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }
    }
}
