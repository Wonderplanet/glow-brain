using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Models.Item
{
    public record ItemExchangeSelectItemResultModel(
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<RewardModel> ItemRewardModels);
}
