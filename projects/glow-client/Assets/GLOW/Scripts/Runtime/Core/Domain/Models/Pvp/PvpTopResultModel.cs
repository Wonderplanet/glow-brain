using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpTopResultModel(
        PvpHeldStatusModel PvpHeldStatus,
        UserPvpStatusModel UsrPvpStatus,
        IReadOnlyList<OpponentSelectStatusModel> OpponentSelectStatuses,
        PvpPreviousSeasonResultModel PvpPreviousSeasonResult,
        ViewableRankingFromCalculatingFlag IsViewableRankingFromCalculating
    )
    {
        public static PvpTopResultModel Empty { get; } = new(
            PvpHeldStatusModel.Empty,
            UserPvpStatusModel.Empty,
            new List<OpponentSelectStatusModel>(),
            PvpPreviousSeasonResultModel.Empty,
            ViewableRankingFromCalculatingFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
