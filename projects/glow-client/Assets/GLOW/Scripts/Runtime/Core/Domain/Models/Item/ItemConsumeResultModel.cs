using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Models.Item
{
    public record ItemConsumeResultModel(
        IReadOnlyList<UserItemModel> UserItemModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<RewardModel> ItemRewardModels,
        UserItemTradeModel UserItemTradeModel)
    {
        public static ItemConsumeResultModel Empty { get; } = new(
            new List<UserItemModel>(),
            UserParameterModel.Empty,
            new List<RewardModel>(),
            UserItemTradeModel.Empty);
    }
}
