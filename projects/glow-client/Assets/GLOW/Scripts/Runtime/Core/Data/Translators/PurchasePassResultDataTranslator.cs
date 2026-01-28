using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Translators
{
    public class PurchasePassResultDataTranslator
    {
        public static PurchasePassResultModel ToPurchasePassResultModel(
            PurchasePassResultData purchaseResultPassData)
        {
            var storeData = purchaseResultPassData.UsrStoreProduct;
            var userStoreModel = new UserStoreProductModel(
                new MasterDataId(storeData.ProductSubId),
                new PurchaseCount(storeData.PurchaseCount),
                new PurchaseCount(storeData.PurchaseTotalCount));

            var userParameterModel = UserParameterTranslator.ToUserParameterModel(purchaseResultPassData.UsrParameter);

            var userShopPassModels = UserShopPassDataTranslator.ToUserShopPassModel(purchaseResultPassData.UsrShopPass);

            var userStoreInfoModel = UserStoreInfoModelTranslator.ToUserStoreInfoModel(purchaseResultPassData.UsrStoreInfo);

            return new PurchasePassResultModel(
                userStoreModel,
                userParameterModel,
                userShopPassModels,
                userStoreInfoModel);
        }
    }
}
