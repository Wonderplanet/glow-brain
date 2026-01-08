using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record EncyclopediaReceiveRewardResultModel(
        IReadOnlyList<UserReceivedUnitEncyclopediaRewardModel> UserReceivedUnitEncyclopediaRewards,
        IReadOnlyList<RewardModel> UnitEncyclopediaRewards,
        UserParameterModel UserParameter,
        IReadOnlyList<UserItemModel> UserItems,
        UserLevelUpResultModel UserLevelUp,
        IReadOnlyList<UserConditionPackModel> UserConditionPacks,
        IsEmblemDuplicated IsEmblemDuplicated
        );
}
