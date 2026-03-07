using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
namespace GLOW.Scenes.PvpRanking.Domain.Models
{
    public record PvpRankingMyselfUserUseCaseModel(
        UserName UserName,
        PvpPoint TotalPoint,
        EmblemAssetKey EmblemAssetKey,
        UnitAssetKey UnitAssetKey,
        PvpRankingRank Rank,
        PvpRankClassType RankClassType,
        PvpRankLevel RankLevel,
        PvpRankingCalculatingFlag CalculatingRankings,
        PvpRankingInEntryFlag IsInEntry,
        PvpExcludeRankingFlag IsExcludeRanking,
        PvpRankingAchieveRankingFlag IsAchieveRanking,
        PvpUserRankStatus PvpUserRankStatus)
    {
        public static PvpRankingMyselfUserUseCaseModel Empty { get; } = new (
            UserName.Empty,
            PvpPoint.Empty,
            EmblemAssetKey.Empty,
            UnitAssetKey.Empty,
            PvpRankingRank.Empty,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpRankingCalculatingFlag.False,
            PvpRankingInEntryFlag.False,
            PvpExcludeRankingFlag.False,
            PvpRankingAchieveRankingFlag.False,
            PvpUserRankStatus.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
