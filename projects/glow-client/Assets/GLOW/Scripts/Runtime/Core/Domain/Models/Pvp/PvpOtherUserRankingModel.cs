using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpOtherUserRankingModel(
        UserMyId UserMyId,
        PvpRankingRank Rank,
        PvpPoint Score,
        UserName Name,
        MasterDataId MstUnitId,
        MasterDataId MstEmblemId
    )
    {
        public static PvpOtherUserRankingModel Empty { get; } = new(
            UserMyId.Empty,
            PvpRankingRank.Empty,
            PvpPoint.Empty,
            UserName.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
