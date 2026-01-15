using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PackShopGachaBannerAssetPath(string Value)
    {
        const string AssetPathFormat = "gachabanner/gacha_banner_{0}.png";

        public static PackShopGachaBannerAssetPath Empty { get; } = new PackShopGachaBannerAssetPath(string.Empty);

        public static PackShopGachaBannerAssetPath FromAssetKey(GachaBannerAssetKey assetKey)
        {
            return new PackShopGachaBannerAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
