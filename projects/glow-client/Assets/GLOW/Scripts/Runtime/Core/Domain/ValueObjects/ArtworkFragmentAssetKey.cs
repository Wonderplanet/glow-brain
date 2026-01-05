using Cysharp.Text;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkFragmentAssetKey(string Value)
    {
        const string AssetKeyFormat = "icon_{0:D2}";

        public PlayerResourceAssetKey ToPlayerResourceAssetKey()
        {
            return new PlayerResourceAssetKey(Value);
        }

        public static ArtworkFragmentAssetKey ToArtworkFragmentAssetKey(ArtworkFragmentAssetNum num)
        {
            return new ArtworkFragmentAssetKey(ZString.Format(AssetKeyFormat, num.Value));
        }
    }
}
