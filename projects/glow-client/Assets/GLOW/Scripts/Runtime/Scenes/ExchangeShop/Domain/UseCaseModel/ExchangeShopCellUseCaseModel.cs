using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCaseModel
{
    public record ExchangeShopCellUseCaseModel(
        MasterDataId MstExchangeShopId,
        MasterDataId MstLineupId,
        RemainingTimeSpan RemainingTime,
        PurchasableCount PurchasableCount,
        ProductName ProductItemName,
        ResourceType ProductResourceType,
        ProductResourceAmount ProductResourceAmount,
        ItemIconAssetPath ProductItemIconAssetPath,
        ItemName CostItemName,
        ExchangeCostType ExchangeCostType,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount CostAmount,
        SortOrder SortOrder,
        PlayerResourceModel PlayerResourceModel);
}
