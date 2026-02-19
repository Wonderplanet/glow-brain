using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using ResourceType = GLOW.Core.Domain.Constants.ResourceType;

namespace GLOW.Scenes.ExchangeShop.Presentation.ViewModel
{
    public record ExchangeShopCellViewModel(
        MasterDataId MstExchangeShopId,
        MasterDataId MstLineupId,
        ProductName ProductName,
        ResourceType ProductResourceType,
        ProductResourceAmount ProductResourceAmount,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        RemainingTimeSpan LimitTime,
        PurchasableCount PurchasableCount,
        ItemIconAssetPath CostItemIconAssetPath,
        ItemAmount CostItemAmount,
        SortOrder SortOrder
    )
    {
        public static ExchangeShopCellViewModel Empty { get; } = new ExchangeShopCellViewModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            new ProductName(""),
            ResourceType.Item,
            ProductResourceAmount.Empty,
            PlayerResourceIconViewModel.Empty,
            RemainingTimeSpan.Empty,
            PurchasableCount.Empty,
            ItemIconAssetPath.Empty,
            ItemAmount.Empty,
            SortOrder.Empty
        );
    }
}
