using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleTopResultModel(
        IReadOnlyList<AdventBattleRewardModel> AdventBattleMaxScoreRewards,
        IReadOnlyList<AdventBattleRewardModel> AdventBattleRaidTotalScoreRewards,
        UserParameterModel UserParameter,
        IReadOnlyList<UserItemModel> UserItems,
        IReadOnlyList<UserEmblemModel> UserEmblems)
    {
        public static AdventBattleTopResultModel Empty { get; } = new(
            new List<AdventBattleRewardModel>(),
            new List<AdventBattleRewardModel>(),
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserEmblemModel>());
    }
}