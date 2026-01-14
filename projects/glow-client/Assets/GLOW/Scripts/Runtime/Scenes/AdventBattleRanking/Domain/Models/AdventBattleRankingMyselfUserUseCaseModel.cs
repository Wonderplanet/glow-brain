using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
namespace GLOW.Scenes.AdventBattleRanking.Domain.Models
{
    public record AdventBattleRankingMyselfUserUseCaseModel(
        UserName UserName,
        AdventBattleScore MaxScore,
        EmblemAssetKey EmblemAssetKey,
        UnitAssetKey UnitAssetKey,
        AdventBattleRankingRank Rank,
        RankType RankType,
        AdventBattleScoreRankLevel RankLevel,
        AdventBattleRankingCalculatingFlag CalculatingRankings,
        AdventBattleRankingInEntryFlag IsInEntry,
        AdventBattleRankingExcludeRankingFlag IsExcludeRanking,
        AdventBattleRankingAchieveRankingFlag IsAchieveRanking)
    {
        public static AdventBattleRankingMyselfUserUseCaseModel Empty { get; } = new (
            UserName.Empty,
            AdventBattleScore.Empty,
            EmblemAssetKey.Empty,
            UnitAssetKey.Empty,
            AdventBattleRankingRank.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleRankingCalculatingFlag.False,
            AdventBattleRankingInEntryFlag.False,
            AdventBattleRankingExcludeRankingFlag.False,
            AdventBattleRankingAchieveRankingFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
