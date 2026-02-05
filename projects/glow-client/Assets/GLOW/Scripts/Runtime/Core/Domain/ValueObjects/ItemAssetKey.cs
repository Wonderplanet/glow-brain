using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemAssetKey(ObscuredString Value)
    {
        public static ItemAssetKey Empty { get; } = new ItemAssetKey("");

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
