using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Core.Domain.Updaters
{
    public interface IShopPurchaseResultUpdater
    {
        void UpdatePurchaseResult(PurchaseResultModel purchaseResult);

        void UpdatePurchasePassResult(PurchasePassResultModel purchasePassResult);
    }

    public class ShopPurchaseResultUpdater : IShopPurchaseResultUpdater
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public void UpdatePurchaseResult(PurchaseResultModel purchaseResult)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var currentParameterModel = purchaseResult.UserParameterModel;
            var stageModels = fetchModel.StageModels;
            var badgeModel = fetchModel.BadgeModel;
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = currentParameterModel,
                StageModels = stageModels,
                BadgeModel = badgeModel
            };

            var userItemModels = purchaseResult.UserItemModels;
            var userUnitModels = purchaseResult.UserUnitModels;
            var userTradePackModels = purchaseResult.UserTradePackModels;
            var userStoreInfoModel = purchaseResult.UserStoreInfoModel.IsEmpty()
                ? fetchOtherModel.UserStoreInfoModel
                : purchaseResult.UserStoreInfoModel;

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserItemModels = fetchOtherModel.UserItemModels.Update(userItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(userUnitModels),
                UserStoreProductModels = fetchOtherModel.UserStoreProductModels.Update(purchaseResult.UserStoreProductModel),
                UserTradePackModels = fetchOtherModel.UserTradePackModels.Update(userTradePackModels),
                UserStoreInfoModel = userStoreInfoModel
            };

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }

        public void UpdatePurchasePassResult(PurchasePassResultModel purchasePassResult)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var currentParameterModel = purchasePassResult.UserParameterModel;
            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = currentParameterModel,
            };

            var userStoreInfoModel = purchasePassResult.UserStoreInfoModel.IsEmpty()
                ? fetchOtherModel.UserStoreInfoModel
                : purchasePassResult.UserStoreInfoModel;

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserStoreProductModels = fetchOtherModel.UserStoreProductModels.Update(purchasePassResult.UserStoreProductModel),
                UserShopPassModels = fetchOtherModel.UserShopPassModels.Update(purchasePassResult.UserShopPassModel),
                UserStoreInfoModel = userStoreInfoModel
            };

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }
    }
}
