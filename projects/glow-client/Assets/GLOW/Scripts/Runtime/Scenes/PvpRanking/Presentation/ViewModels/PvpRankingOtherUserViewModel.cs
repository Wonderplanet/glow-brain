using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
namespace GLOW.Scenes.PvpRanking.Presentation.ViewModels
{
    public record PvpRankingOtherUserViewModel(
        UserName UserName,
        PvpPoint Score,
        EmblemIconAssetPath EmblemIconAssetPath,
        CharacterIconAssetPath UnitIconAssetPath,
        PvpRankingRank Rank,
        PvpRankingMyselfFlag IsMyself,
        PvpRankClassType RankClassType,
        PvpRankLevel RankLevel,
        PvpUserRankStatus PvpUserRankStatus)
    {
        public static PvpRankingOtherUserViewModel Empty { get; } = new (
            UserName.Empty,
            PvpPoint.Empty,
            EmblemIconAssetPath.Empty,
            CharacterIconAssetPath.Empty,
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
