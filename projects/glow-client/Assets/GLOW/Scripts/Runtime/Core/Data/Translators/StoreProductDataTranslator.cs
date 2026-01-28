using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using WPFramework.Constants.Platform;
using WPFramework.Domain.Modules;

namespace GLOW.Core.Data.Translators
{
    public static class StoreProductDataTranslator
    {
        public static MstStoreProductModel ToStoreProductModel(MstStoreProductData mstStoreProductData,
            MstStoreProductI18nData i18n,
            OprProductData oprProductData,
            OprProductI18nData oprProductI18n,
            ISystemInfoProvider systemInfoProvider)
        {
            var platform = systemInfoProvider.GetApplicationSystemInfo();
            var price = platform.PlatformId == PlatformId.Android ? i18n.PriceAndroid : i18n.PriceIos;
            var productId = platform.PlatformId == PlatformId.Android ? mstStoreProductData.ProductIdAndroid : mstStoreProductData.ProductIdIos;
            var assetKey = string.IsNullOrEmpty(oprProductI18n.AssetKey) ?
                ShopProductAssetKey.Empty :
                new ShopProductAssetKey(oprProductI18n.AssetKey);

            return new MstStoreProductModel(
                new MasterDataId(mstStoreProductData.Id),
                new MasterDataId(oprProductData.Id),
                new ShopProductId(productId),
                new ProductPrice(price),
                oprProductData.ProductType,
                new PurchasableCount(oprProductData.PurchasableCount.HasValue ? oprProductData.PurchasableCount.Value : -1),
                new ProductResourceAmount(oprProductData.PaidAmount),
                new SortOrder(oprProductData.DisplayPriority),
                oprProductData.StartDate,
                oprProductData.EndDate,
                assetKey);
        }
    }
}
