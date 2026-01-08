using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Translators
{
    public class PurchaseResultDataTranslator
    {
        public static PurchaseResultModel ToPurchaseResultModel(
            PurchaseResultData purchaseResultData)
        {
            // PurchaseResultRewardDataはRewardDataだけ持ってるので直接RewardModelに変換する
            // 他のパラメータを持つようになったら専用のModelを用意する
            var rewards = purchaseResultData.Rewards
                .Select(reward => RewardDataTranslator.Translate(reward.Reward))
                .ToList();

            var storeData = purchaseResultData.UsrStoreProduct;
            var userStoreModels = storeData == null ?
                UserStoreProductModel.Empty :
                new UserStoreProductModel(
                    new MasterDataId(storeData.ProductSubId),
                    new PurchaseCount(storeData.PurchaseCount),
                    new PurchaseCount(storeData.PurchaseTotalCount));

            var itemData = purchaseResultData.UsrItems;
            var userItemModels = itemData
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var parameterData = purchaseResultData.UsrParameter;
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(parameterData);

            var unitData = purchaseResultData.UsrUnits;
            var unitModels = unitData
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var tradePacks = purchaseResultData.UsrTradePacks
                ?.Select(UserTradePackDataTranslator.Translate)
                .ToList()
                ?? new List<UserTradePackModel>();

            var userStoreInfoModel = UserStoreInfoModelTranslator.ToUserStoreInfoModel(purchaseResultData.UsrStoreInfo);

            return new PurchaseResultModel(
                rewards,
                userStoreModels,
                tradePacks,
                userParameterModel,
                userItemModels,
                unitModels,
                userStoreInfoModel);

        }
    }
}
