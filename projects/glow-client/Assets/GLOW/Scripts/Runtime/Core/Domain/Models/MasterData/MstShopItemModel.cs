using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstShopItemModel(
        MasterDataId Id,
        ShopType ShopType,
        CostType CostType,
        CostAmount CostAmount,
        IsFirstTimeFree IsFirstTimeFree,
        PurchasableCount PurchasableCount,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ProductResourceAmount ProductResourceAmount,
        ObscuredDateTimeOffset StartDate,
        ObscuredDateTimeOffset EndDate)
    {
        public static MstShopItemModel Empty { get; } = new(
            MasterDataId.Empty,
            ShopType.Coin,
            CostType.Coin,
            CostAmount.Empty,
            new IsFirstTimeFree(false),
            PurchasableCount.Empty,
            ResourceType.Coin,
            MasterDataId.Empty,
            ProductResourceAmount.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
