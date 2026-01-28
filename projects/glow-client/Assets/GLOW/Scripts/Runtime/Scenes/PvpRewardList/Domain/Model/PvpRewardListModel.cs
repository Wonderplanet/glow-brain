using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpRewardList.Domain.Model
{
    public record PvpRewardListModel(
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<IPvpRankingRewardModel> RankingRewards,
        IReadOnlyList<PvpPointRankRewardModel> PointRankRewards,
        IReadOnlyList<PvpTotalScoreRewardModel> TotalScoreRewards)
    {
        public static PvpRewardListModel Empty { get; } = 
            new PvpRewardListModel(
                RemainingTimeSpan.Empty,
                new List<IPvpRankingRewardModel>(),
                new List<PvpPointRankRewardModel>(),
                new List<PvpTotalScoreRewardModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}