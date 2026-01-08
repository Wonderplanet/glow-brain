using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record StoreProductModel(
        MasterDataId OprProductId,
        DisplayCostType DisplayCostType,
        ProductPrice Price,
        RawProductPriceText RawProductPriceText,
        ProductType ProductType,
        PurchasableCount PurchasableCount,
        RemainingTimeSpan PurchasableTime,
        NewFlag NewFlag,
        ProductResourceAmount PaidResourceAmount,
        PlayerResourceModel PlayerResourceModel,
        ShopProductAssetPath ShopProductAssetPath)
    {
        public static StoreProductModel Empty { get; } = new(
            MasterDataId.Empty,
            DisplayCostType.Coin,
            ProductPrice.Empty,
            RawProductPriceText.Empty,
            ProductType.Diamond,
            PurchasableCount.Empty,
            RemainingTimeSpan.Empty,
            NewFlag.Empty,
            ProductResourceAmount.Empty,
            PlayerResourceModel.Empty,
            ShopProductAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
