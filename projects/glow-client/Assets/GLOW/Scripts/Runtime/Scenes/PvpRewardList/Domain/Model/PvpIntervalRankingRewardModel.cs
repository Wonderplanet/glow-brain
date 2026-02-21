using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public record PvpIntervalRankingRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        PvpRankingRank RankingRankLower,
        PvpRankingRank RankingRankUpper) : IPvpRankingRewardModel
    {
        public static PvpIntervalRankingRewardModel Empty { get; } = 
            new PvpIntervalRankingRewardModel(
                MasterDataId.Empty,
                new List<PlayerResourceModel>(),
                PvpRankingRank.Empty,
                PvpRankingRank.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        PvpRewardCategory IPvpRankingRewardModel.RewardCategory => PvpRewardCategory.RankClass;
    }
}