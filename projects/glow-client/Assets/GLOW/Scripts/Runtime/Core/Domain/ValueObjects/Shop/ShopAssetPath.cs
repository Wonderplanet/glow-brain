using Cysharp.Text;
using GLOW.Core.Domain.Constants.Shop;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ShopAssetPath(string Value)
    {
        const string CategoryBannerPathFormat = "shop_category_banner_{0}";

        public static ShopAssetPath FromShopProductCategory(DisplayShopProductType category)
        {
            return new ShopAssetPath(ZString.Format(CategoryBannerPathFormat, category.ToString().ToLower()));
        }
    }
}
