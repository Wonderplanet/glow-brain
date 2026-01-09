using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models
{
    public record UserPvpStatusModel(
        PvpPoint Score,
        PvpPoint MaxReceivedTotalScore,
        PvpRankClassType PvpRankClassType,
        PvpRankLevel RankLevel,
        PvpDailyChallengeCount RemainingChallengeCount,
        PvpDailyChallengeCount RemainingItemChallengeCount
    )
    {
        public static UserPvpStatusModel Empty { get; } = new(
            PvpPoint.Zero,
            PvpPoint.Zero,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpDailyChallengeCount.Empty,
            PvpDailyChallengeCount.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
