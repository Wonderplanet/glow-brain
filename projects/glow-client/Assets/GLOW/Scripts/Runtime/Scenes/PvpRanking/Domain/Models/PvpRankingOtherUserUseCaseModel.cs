using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
namespace GLOW.Scenes.PvpRanking.Domain.Models
{
    public record PvpRankingOtherUserUseCaseModel(
        UserMyId UserMyId,
        UserName UserName,
        PvpPoint TotalPoint,
        EmblemAssetKey EmblemAssetKey,
        UnitAssetKey UnitAssetKey,
        PvpRankingRank Rank,
        PvpRankingMyselfFlag IsMyself,
        PvpRankClassType RankClassType,
        PvpRankLevel RankLevel,
        PvpUserRankStatus PvpUserRankStatus)
    {
        public static PvpRankingOtherUserUseCaseModel Empty { get; } = new(
            UserMyId.Empty,
            UserName.Empty,
            PvpPoint.Empty,
            EmblemAssetKey.Empty,
            UnitAssetKey.Empty,
            PvpRankingRank.Empty,
            PvpRankingMyselfFlag.False,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpUserRankStatus.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
