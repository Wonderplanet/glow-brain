using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpRanking.Presentation.Constants;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
namespace GLOW.Scenes.PvpRanking.Presentation.ViewModels
{
    public record PvpRankingMyselfUserViewModel(
        UserName UserName,
        PvpPoint Score,
        EmblemIconAssetPath EmblemIconAssetPath,
        CharacterIconAssetPath UnitIconAssetPath,
        PvpRankingRank Rank,
        PvpRankClassType RankClassType,
        PvpRankLevel RankLevel,
        PvpRankingCalculatingFlag CalculatingRankings,
        PvpRankingMyselfUserViewStatus ViewStatus,
        PvpUserRankStatus PvpUserRankStatus)
    {
        public static PvpRankingMyselfUserViewModel Empty { get; } = new (
            UserName.Empty,
            PvpPoint.Empty,
            EmblemIconAssetPath.Empty,
            CharacterIconAssetPath.Empty,
            PvpRankingRank.Empty,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpRankingCalculatingFlag.False,
            PvpRankingMyselfUserViewStatus.Normal,
            PvpUserRankStatus.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
