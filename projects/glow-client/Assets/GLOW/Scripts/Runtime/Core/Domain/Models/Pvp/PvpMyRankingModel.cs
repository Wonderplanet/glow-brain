using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpMyRankingModel(
        PvpRankingRank Rank,
        PvpPoint Score,
        PvpExcludeRankingFlag IsExcludeRanking
    )
    {
        public static PvpMyRankingModel Empty { get; } = new(
            PvpRankingRank.Empty,
            PvpPoint.Empty,
            PvpExcludeRankingFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
