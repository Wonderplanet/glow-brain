using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeConfirmViewModel(
        ItemName ExchangeItemName,
        ItemAmount ExchangeItemAmount,
        ItemName CostItemName,
        ItemAmount CostItemAmount,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount CurrentCostItemAmount,
        PurchaseCount MaxPurchaseCount,
        PurchaseCount CurrentMaxPurchaseCount,
        RemainingTimeSpan LimitTime)
    {
        public static ExchangeConfirmViewModel Empty { get; } =
            new ExchangeConfirmViewModel(
                new ItemName(""),
                ItemAmount.Empty,
                new ItemName(""),
                ItemAmount.Empty,
                ItemIconAssetPath.Empty,
                ItemAmount.Empty,
                PurchaseCount.Empty,
                PurchaseCount.Empty,
                RemainingTimeSpan.Empty);
    }
}
