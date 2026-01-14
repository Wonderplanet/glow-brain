using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record HomeBannerAssetPath(string Value)
    {
        const string AssetPathFormat = "homebanner/{0}.png";
        public static HomeBannerAssetPath CreateAssetPath(HomeBannerAssetKey key) => new HomeBannerAssetPath(ZString.Format(AssetPathFormat, key.Value));
    };
}
