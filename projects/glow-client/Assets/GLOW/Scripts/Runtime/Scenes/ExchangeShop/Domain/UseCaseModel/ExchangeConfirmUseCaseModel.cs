using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCaseModel
{
    public record ExchangeConfirmUseCaseModel(
        MasterDataId ExchangeItemId,
        ItemName ExchangeItemName,
        ItemAmount ExchangeItemAmount,
        ItemIconAssetPath ExchangeItemIconAssetPath,
        Rarity ExchangeItemRarity,
        ItemName CostItemName,
        ItemAmount CostItemAmount,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount CurrentCostItemAmount,
        PurchaseCount MaxPurchaseCount,
        PurchaseCount CurrentMaxPurchaseCount,
        RemainingTimeSpan LimitTime);
}
