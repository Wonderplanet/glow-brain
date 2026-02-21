using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpTopUserState(
        PvpRankingUserJoinType PvpRankingUserJoinType,
        PvpPoint TotalPoint,
        PvpPoint NextRankUpPoint,
        PvpUserRankStatus PvpUserRankStatus,
        PvpChallengeStatus PvpChallengeStatus
    )
    {
        public static PvpTopUserState Empty { get; } = new(
            PvpRankingUserJoinType.CannotJoin,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpUserRankStatus.Empty,
            PvpChallengeStatus.Empty
        );
    };
}