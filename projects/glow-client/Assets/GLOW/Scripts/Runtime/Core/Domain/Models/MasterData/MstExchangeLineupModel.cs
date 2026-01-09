using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;

namespace GLOW.Core.Domain.Models
{
    public record MstExchangeLineupModel(
        MasterDataId MstLineupId,
        PurchasableCount PurchasableCount,
        MasterDataId ProductItemId,
        ResourceType ProductResourceType,
        ProductResourceAmount ResourceAmount,
        MasterDataId CostItemId,
        ExchangeCostType ExchangeCostType,
        ItemAmount CostAmount,
        ExchangeShopEndTime EndAt,
        SortOrder SortOrder)
    {
    public static MstExchangeLineupModel Empty { get; } = new MstExchangeLineupModel(
        MasterDataId.Empty,
        PurchasableCount.Empty,
        MasterDataId.Empty,
        ResourceType.Item,
        ProductResourceAmount.Empty,
        MasterDataId.Empty,
        ExchangeCostType.Item,
        ItemAmount.Empty,
        ExchangeShopEndTime.Empty,
        SortOrder.Empty
    );
    }
}
