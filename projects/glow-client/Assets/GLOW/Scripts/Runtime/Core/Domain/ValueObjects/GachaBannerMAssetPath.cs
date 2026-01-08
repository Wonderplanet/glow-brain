using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record GachaBannerMAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_banner_m_{0}";

        public static GachaBannerMAssetPath FromAssetKey(GachaBannerAssetKey assetKey)
        {
            return new GachaBannerMAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}
