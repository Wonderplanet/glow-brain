using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRanking.Domain.ValueObjects;
namespace GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels
{
    public record AdventBattleRankingOtherUserViewModel(
        UserName UserName,
        AdventBattleScore MaxScore,
        EmblemIconAssetPath EmblemIconAssetPath,
        CharacterIconAssetPath UnitIconAssetPath,
        AdventBattleRankingRank Rank,
        AdventBattleRankingMyselfFlag IsMyself,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel)
    {
        public static AdventBattleRankingOtherUserViewModel Empty { get; } = new (
            UserName.Empty,
            AdventBattleScore.Empty,
            EmblemIconAssetPath.Empty,
            CharacterIconAssetPath.Empty,
            AdventBattleRankingRank.Empty,
            AdventBattleRankingMyselfFlag.False,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
