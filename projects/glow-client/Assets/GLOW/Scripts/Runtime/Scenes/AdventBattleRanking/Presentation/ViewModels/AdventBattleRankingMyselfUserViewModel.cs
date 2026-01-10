using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRanking.Presentation.Constants;
namespace GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels
{
    public record AdventBattleRankingMyselfUserViewModel(
        UserName UserName,
        AdventBattleScore MaxScore,
        EmblemIconAssetPath EmblemIconAssetPath,
        CharacterIconAssetPath UnitIconAssetPath,
        AdventBattleRankingRank Rank,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        AdventBattleRankingCalculatingFlag CalculatingRankings,
        AdventBattleRankingMyselfUserViewStatus ViewStatus)
    {
        public static AdventBattleRankingMyselfUserViewModel Empty { get; } = new (
            UserName.Empty,
            AdventBattleScore.Empty,
            EmblemIconAssetPath.Empty,
            CharacterIconAssetPath.Empty,
            AdventBattleRankingRank.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleRankingCalculatingFlag.False,
            AdventBattleRankingMyselfUserViewStatus.Normal);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
