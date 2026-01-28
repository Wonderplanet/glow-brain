using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ShopProductAssetPath(string Value)
    {
        static string AssetPathFormat => "shop_product_{0}";
        static string AssetPathDiamondFormat => "shop_product_prism_{0}";
        static string AssetPathCoinFormat => "shop_product_coin_{0}";

        public static ShopProductAssetPath Empty { get; } = new(string.Empty);

        public static ShopProductAssetPath FromAssetKey(ShopProductAssetKey assetKey)
        {
            return new ShopProductAssetPath(ZString.Format(AssetPathFormat, assetKey.Value));
        }

        public static ShopProductAssetPath CreateDiamond(int index)
        {
            return new ShopProductAssetPath(ZString.Format(AssetPathDiamondFormat, index.ToString("D5")));
        }

        public static ShopProductAssetPath CreateCoin(int index)
        {
            return new ShopProductAssetPath(ZString.Format(AssetPathCoinFormat, index.ToString("D5")));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
