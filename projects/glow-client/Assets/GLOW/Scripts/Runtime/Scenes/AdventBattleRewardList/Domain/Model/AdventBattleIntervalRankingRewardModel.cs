using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public record AdventBattleIntervalRankingRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        AdventBattleRankingRank RankingRankLower,
        AdventBattleRankingRank RankingRankUpper) : IAdventBattlePersonalRewardModel
    {
        public static AdventBattleIntervalRankingRewardModel Empty { get; } = 
            new AdventBattleIntervalRankingRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                AdventBattleRankingRank.Empty,
                AdventBattleRankingRank.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AdventBattleRewardCategory IAdventBattlePersonalRewardModel.RewardCategory =>
            AdventBattleRewardCategory.Ranking;
    }
}