using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkFragmentAssetPath(string Value)
    {
        const string AssetPathFormat = "artwork_fragment_{0}";

        public static ArtworkFragmentAssetPath FromAssetKey(PlayerResourceAssetKey key)
        {
            return new ArtworkFragmentAssetPath(ZString.Format(AssetPathFormat, key.Value));
        }

        public PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath()
        {
            return new PlayerResourceIconAssetPath(Value);
        }
    }
}
