using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleMyRankingModel(
        AdventBattleRankingRank Rank,
        AdventBattleScore MaxScore,
        AdventBattleScore TotalScore,
        AdventBattleRankingExcludeRankingFlag IsExcludeRanking)
    {
        public static AdventBattleMyRankingModel Empty { get; } = new(
            AdventBattleRankingRank.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleRankingExcludeRankingFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}