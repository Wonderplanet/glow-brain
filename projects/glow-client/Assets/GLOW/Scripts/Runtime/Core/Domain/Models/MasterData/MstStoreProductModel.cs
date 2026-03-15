using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record MstStoreProductModel(
        MasterDataId Id,
        MasterDataId OprProductId,
        ShopProductId ProductId,
        ProductPrice Price,
        ProductType ProductType,
        PurchasableCount PurchasableCount,
        ProductResourceAmount PaidAmount,
        SortOrder DisplayPriority,
        DateTimeOffset StartDate,
        DateTimeOffset EndDate,
        ShopProductAssetKey ShopProductAssetKey
    )
    {
        public static MstStoreProductModel Empty { get; } = new MstStoreProductModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ShopProductId.Empty,
            ProductPrice.Empty,
            ProductType.Diamond,
            PurchasableCount.Empty,
            ProductResourceAmount.Empty,
            SortOrder.Zero,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue,
            ShopProductAssetKey.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool ShouldDisplay()
        {
            return DisplayPriority.Value != 0;
        }
    };
}
