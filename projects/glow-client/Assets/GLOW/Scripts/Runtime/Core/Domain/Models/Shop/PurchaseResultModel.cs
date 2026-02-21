using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Shop
{
    public record PurchaseResultModel(
        IReadOnlyList<RewardModel> Rewards,
        UserStoreProductModel UserStoreProductModel,
        IReadOnlyList<UserTradePackModel> UserTradePackModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        UserStoreInfoModel UserStoreInfoModel)
    {
        public static PurchaseResultModel Empty { get; } = new(
            new List<RewardModel>(),
            UserStoreProductModel.Empty,
            new List<UserTradePackModel>(),
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserUnitModel>(),
            UserStoreInfoModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
