using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaBannerAssetPath(string Value)
    {
        const string AssetPathFormat = "gachabanner/gacha_banner_{0}.png";

        public static GachaBannerAssetPath Empty { get; } = new GachaBannerAssetPath(string.Empty);

        public static GachaBannerAssetPath FromAssetKey(GachaBannerAssetKey assetKey)
        {
            return new GachaBannerAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
