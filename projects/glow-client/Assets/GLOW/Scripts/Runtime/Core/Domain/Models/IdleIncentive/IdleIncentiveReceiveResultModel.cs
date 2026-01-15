using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.IdleIncentive
{
    public record IdleIncentiveReceiveResultModel(
        IReadOnlyList<RewardModel> Rewards,
        UserLevelUpResultModel UserLevel,
        UserIdleIncentiveModel UserIdleIncentive,
        UserParameterModel UsrParameter,
        IReadOnlyList<UserItemModel> UsrItems,
        IReadOnlyList<UserConditionPackModel> UserConditionPacks);
}
