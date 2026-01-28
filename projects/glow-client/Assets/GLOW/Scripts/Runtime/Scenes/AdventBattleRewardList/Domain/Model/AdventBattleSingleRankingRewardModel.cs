using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public record AdventBattleSingleRankingRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        AdventBattleRankingRank RankingRank) : IAdventBattlePersonalRewardModel
    {
        public static AdventBattleSingleRankingRewardModel Empty { get; } = 
            new AdventBattleSingleRankingRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                AdventBattleRankingRank.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        AdventBattleRewardCategory IAdventBattlePersonalRewardModel.RewardCategory =>
            AdventBattleRewardCategory.Ranking;
    }
}