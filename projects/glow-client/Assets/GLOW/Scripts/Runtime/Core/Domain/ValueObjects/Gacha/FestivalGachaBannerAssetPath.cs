using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record FestivalGachaBannerAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_banner_{0}";

        public static FestivalGachaBannerAssetPath Empty { get; } = new FestivalGachaBannerAssetPath(string.Empty);

        public static FestivalGachaBannerAssetPath FromAssetKey(GachaBannerAssetKey assetKey)
        {
            return new FestivalGachaBannerAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }
    }
}

