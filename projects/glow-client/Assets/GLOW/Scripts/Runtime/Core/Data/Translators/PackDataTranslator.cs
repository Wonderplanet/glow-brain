using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Data.Translators
{
    public static class PackDataTranslator
    {
        public static MstPackModel ToPackModel(MstPackData mstPackData, MstPackI18nData mstPackI18NData)
        {
            var saleCondition = mstPackData.SaleCondition.HasValue
                 ? new SaleConditionValue(mstPackData.SaleCondition, mstPackData.SaleConditionValue)
                 : SaleConditionValue.Empty;
            var saleHour = mstPackData.SaleHours.HasValue
                ? new SaleHour(mstPackData.SaleHours.Value)
                : SaleHour.Empty;
            var packDecoration = mstPackData.PackDecoration.HasValue
                ? mstPackData.PackDecoration
                : null;
            var tradableCount = mstPackData.TradableCount.HasValue
                ? new PurchasableCount(mstPackData.TradableCount.Value)
                : PurchasableCount.Empty;

            var isDisplayExpiration = mstPackData.IsDisplayExpiration
                ? DisplayExpirationFlag.True
                : DisplayExpirationFlag.False;

            var isFirstTimeFree = mstPackData.IsFirstTimeFree
                ? IsFirstTimeFree.True
                : IsFirstTimeFree.False;

            return new MstPackModel(
                new MasterDataId(mstPackData.Id),
                new MasterDataId(mstPackData.ProductSubId),
                mstPackData.PackType,
                new ProductName(mstPackI18NData.Name),
                tradableCount,
                new DiscountRate(mstPackData.DiscountRate),
                saleCondition,
                saleHour,
                mstPackData.CostType,
                new ProductPrice(mstPackData.CostAmount),
                new RecommendedFlag(mstPackData.IsRecommend),
                new PackBannerAssetKey(mstPackData.AssetKey),
                packDecoration,
                isDisplayExpiration,
                isFirstTimeFree
            );
        }
    }
}
