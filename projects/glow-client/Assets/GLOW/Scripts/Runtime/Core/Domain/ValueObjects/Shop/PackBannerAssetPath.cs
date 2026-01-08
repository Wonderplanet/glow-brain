using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record PackBannerAssetPath(string Value)
    {
        static string AssetPathFormat => "shop_pack_banner_{0}";

        public static PackBannerAssetPath Empty { get; } = new(string.Empty);

        public static PackBannerAssetPath FromAssetKey(PackBannerAssetKey assetKey)
        {
            return new PackBannerAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
