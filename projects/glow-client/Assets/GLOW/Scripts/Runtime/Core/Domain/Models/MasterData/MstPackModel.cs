using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Domain.Models
{
    public record MstPackModel(
        MasterDataId Id,
        MasterDataId ProductSubId,
        PackType PackType,
        ProductName ProductName,
        PurchasableCount TradableCount,
        DiscountRate DiscountRate,
        SaleConditionValue SaleConditionValue,
        SaleHour SaleHours,
        CostType CostType,
        ProductPrice CostAmount,
        RecommendedFlag IsRecommended,
        PackBannerAssetKey BannerAssetKey,
        PackDecoration? PackDecoration,
        DisplayExpirationFlag IsDisplayExpiration,
        IsFirstTimeFree IsFirstTimeFree)
    {
        public static MstPackModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            PackType.Normal,
            ProductName.Empty,
            PurchasableCount.Empty,
            DiscountRate.Empty,
            SaleConditionValue.Empty,
            SaleHour.Empty,
            CostType.Coin,
            ProductPrice.Empty,
            RecommendedFlag.False,
            PackBannerAssetKey.Empty,
            null,
            DisplayExpirationFlag.False,
            IsFirstTimeFree.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
