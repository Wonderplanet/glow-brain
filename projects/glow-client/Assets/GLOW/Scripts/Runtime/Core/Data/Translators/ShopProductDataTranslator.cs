using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Translators
{
    public static class ShopProductDataTranslator
    {
        public static MstShopItemModel ToMstShopProductModel(MstShopItemData data)
        {
            return new MstShopItemModel(
                new MasterDataId(data.Id),
                data.ShopType,
                data.CostType,
                data.CostAmount == null ? CostAmount.Empty : new CostAmount(data.CostAmount.Value),
                new IsFirstTimeFree(data.IsFirstTimeFree),
                new PurchasableCount(data.TradableCount),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ProductResourceAmount(data.ResourceAmount),
                data.StartDate,
                data.EndDate);
        }


    }
}
