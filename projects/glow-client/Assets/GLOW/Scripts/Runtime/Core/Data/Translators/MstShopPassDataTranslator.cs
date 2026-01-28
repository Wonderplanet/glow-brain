using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using WPFramework.Constants.Platform;
using WPFramework.Domain.Modules;

namespace GLOW.Core.Data.Translators
{
    public class MstShopPassDataTranslator
    {
       public static MstShopPassModel ToMstShopPassModel(
           MstShopPassData data,
           MstShopPassI18nData i18nData,
           MstStoreProductModel storeProductModel)
        {
            return new MstShopPassModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.OprProductId),
                storeProductModel.ProductId,
                new PassProductName(i18nData.Name),
                data.ShopPassCellColor,
                new DisplayExpirationFlag(data.IsDisplayExpiration),
                new PassDurationDay(data.PassDurationDays),
                new PassAssetKey(data.AssetKey),
                storeProductModel.Price,
                new PassStartAt(storeProductModel.StartDate),
                new PassEndAt(storeProductModel.EndDate));
        }
    }
}