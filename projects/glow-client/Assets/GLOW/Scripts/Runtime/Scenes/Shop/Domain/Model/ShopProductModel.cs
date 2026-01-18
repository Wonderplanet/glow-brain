using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record ShopProductModel(
        MasterDataId Id,
        MasterDataId ResourceId,
        ProductName ProductName,
        ProductResourceAmount ProductResourceAmount,
        DisplayShopProductType DisplayShopProductType,
        DisplayCostType DisplayCostType,
        CostAmount CostAmount,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay,
        NewFlag NewFlag,
        PurchasableCount PurchasableCount,
        ResourceType ResourceType,
        PlayerResourceModel PlayerResourceModel,
        ItemModel ItemModel,
        ShopProductAssetPath ShopProductAssetPath)
    {
        public static ShopProductModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ProductName.Empty,
            ProductResourceAmount.Empty,
            DisplayShopProductType.Daily,
            DisplayCostType.Free,
            CostAmount.Empty,
            IsFirstTimeFreeDisplay.False,
            NewFlag.Empty,
            PurchasableCount.Empty,
            ResourceType.Coin,
            PlayerResourceModel.Empty,
            ItemModel.Empty,
            ShopProductAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
