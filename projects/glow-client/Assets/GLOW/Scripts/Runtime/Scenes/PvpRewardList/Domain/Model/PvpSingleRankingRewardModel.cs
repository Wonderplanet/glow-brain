using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public record PvpSingleRankingRewardModel(
        MasterDataId Id,
        IReadOnlyList<PlayerResourceModel> Rewards,
        PvpRankingRank RankingRank) : IPvpRankingRewardModel
    {
        PvpRewardCategory IPvpRankingRewardModel.RewardCategory =>
            PvpRewardCategory.Ranking;
    }
}