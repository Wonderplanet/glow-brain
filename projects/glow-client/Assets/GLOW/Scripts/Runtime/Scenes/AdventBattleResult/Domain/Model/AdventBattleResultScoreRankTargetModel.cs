using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.AdventBattleResult.Domain.Model
{
    public record AdventBattleResultScoreRankTargetModel(
        AdventBattleScore BeforeTotalScore,
        AdventBattleScore AfterTotalScore,
        AdventBattleScore TargetRankLowerRequiredScore,
        RankType TargetRankType,
        AdventBattleScoreRankLevel TargetScoreRankLevel,
        AdventBattleScore BeforeLowerRequiredScore)
    {
        public static AdventBattleResultScoreRankTargetModel Empty { get; } = new AdventBattleResultScoreRankTargetModel(
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}