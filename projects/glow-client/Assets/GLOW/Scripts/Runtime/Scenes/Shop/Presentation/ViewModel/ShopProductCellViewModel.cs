using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Shop.Presentation.ViewModel
{
    public record ShopProductCellViewModel(
        MasterDataId ProductId,
        MasterDataId ResourceId,
        DisplayShopProductType DisplayShopProductType,
        ResourceType ResourceType,
        ProductName ProductName,
        ProductResourceAmount ProductResourceAmount,
        ItemIconViewModel ItemIconViewModel,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay,
        PurchasableCount PurchasableCount,
        RemainingTimeSpan PurchasableTerm,
        NewFlag NewFlag,
        DisplayCostType DisplayCostType,
        CostAmount CostAmount,
        ShopProductAssetPath ShopProductAssetPath,
        RawProductPriceText RawProductPriceText
    )
    {
        public ProductResourceAmount DisplayAdvertisementResourceAmount()
        {
            return new(PlayerResourceIconViewModel.Amount.Value);
        }

        public bool IsItemIconInvisible()
        {
            return ItemIconViewModel.IsEmpty();
        }
    }
}
