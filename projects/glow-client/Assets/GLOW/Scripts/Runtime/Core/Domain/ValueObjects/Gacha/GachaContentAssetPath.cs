using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaContentAssetPath(string Value)
    {
        const string AssetPathFormat = "gacha_content_{0}";

        public static GachaContentAssetPath Empty { get; } = new GachaContentAssetPath(string.Empty);

        public static GachaContentAssetPath FromAssetKey(GachaBannerAssetKey assetKey)
        {
            return new GachaContentAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

    }
}
