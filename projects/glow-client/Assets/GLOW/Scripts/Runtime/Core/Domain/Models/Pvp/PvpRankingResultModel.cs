using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpRankingResultModel(
        IReadOnlyList<PvpOtherUserRankingModel> OtherUserRanking,
        PvpMyRankingModel MyRanking
    )
    {
        public static PvpRankingResultModel Empty { get; } = new(
            new List<PvpOtherUserRankingModel>(),
            PvpMyRankingModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
