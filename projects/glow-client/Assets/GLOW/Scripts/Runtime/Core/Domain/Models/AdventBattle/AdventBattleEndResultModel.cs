using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleEndResultModel(
        UserAdventBattleModel UserAdventBattleModel,
        AdventBattleRaidTotalScore TotalScore,
        UserParameterModel UserParameterModel,
        UserLevelUpResultModel UserLevelUpResultModel,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserEnemyDiscoverModel> UserEnemyDiscoverModels,
        IReadOnlyList<AdventBattleRewardModel> AdventBattleDropRewardModels,
        IReadOnlyList<AdventBattleRewardModel> AdventBattleRankRewardModels,
        IReadOnlyList<AdventBattleClearRewardModel> AdventBattleClearRewardModels,
        IReadOnlyList<UserConditionPackModel> UserConditionPackModels)
    {
        public static AdventBattleEndResultModel Empty { get; } = new (
            UserAdventBattleModel.Empty,
            AdventBattleRaidTotalScore.Empty,
            UserParameterModel.Empty,
            UserLevelUpResultModel.Empty,
            new List<UserItemModel>(),
            new List<UserEnemyDiscoverModel>(),
            new List<AdventBattleRewardModel>(),
            new List<AdventBattleRewardModel>(),
            new List<AdventBattleClearRewardModel>(),
            new List<UserConditionPackModel>());
    }
}
