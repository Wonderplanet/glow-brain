using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;

namespace GLOW.Scenes.Shop.Domain.Extension
{
    public static class DisplayShopProductTypeExtension
    {
        public static DisplayShopProductType ToDisplayShopProductType(this ProductType type)
        {
            return type switch
            {
                ProductType.Diamond => DisplayShopProductType.Diamond,
                _ => DisplayShopProductType.Daily
            };
        }

        public static DisplayShopProductType ToDisplayShopProductType(this ShopType type)
        {
            return type switch
            {
                ShopType.Daily => DisplayShopProductType.Daily,
                ShopType.Weekly => DisplayShopProductType.Weekly,
                ShopType.Coin => DisplayShopProductType.Coin,
                _ => DisplayShopProductType.Daily
            };
        }
        
        public static bool HasNextUpdateTime(this DisplayShopProductType type)
        {
            return type switch
            {
                DisplayShopProductType.Diamond => false,
                _ => true
            };
        }
    }
}