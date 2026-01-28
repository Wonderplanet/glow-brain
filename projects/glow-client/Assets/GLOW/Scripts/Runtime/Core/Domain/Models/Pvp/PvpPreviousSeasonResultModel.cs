using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpPreviousSeasonResultModel(
        PvpRankClassType PvpRankClassType,
        PvpRankLevel RankClassLevel,
        PvpPoint Score,
        PvpRankingRank Ranking,
        IReadOnlyList<PvpRewardModel> PvpRewards
    )
    {
        public static PvpPreviousSeasonResultModel Empty { get; } = new(
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpPoint.Zero,
            PvpRankingRank.Empty,
            new List<PvpRewardModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
