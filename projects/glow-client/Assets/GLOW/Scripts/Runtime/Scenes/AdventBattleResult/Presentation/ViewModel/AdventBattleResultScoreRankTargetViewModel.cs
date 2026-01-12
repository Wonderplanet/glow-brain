using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleResult.Presentation.ValueObject;

namespace GLOW.Scenes.AdventBattleResult.Presentation.ViewModel
{
    public record AdventBattleResultScoreRankTargetViewModel(
        AdventBattleScore BeforeTotalScore,
        AdventBattleScore AfterTotalScore,
        AdventBattleScore TargetRankLowerRequiredScore,
        RankType TargetRankType,
        AdventBattleScoreRankLevel TargetScoreRankLevel,
        AdventBattleResultRankAnimationGaugeRate BeforeGaugeRate,
        AdventBattleResultRankAnimationGaugeRate AfterGaugeRate)
    {
        public static AdventBattleResultScoreRankTargetViewModel Empty { get; } = new AdventBattleResultScoreRankTargetViewModel(
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            AdventBattleScore.Empty,
            RankType.Bronze,
            AdventBattleScoreRankLevel.Empty,
            AdventBattleResultRankAnimationGaugeRate.Empty,
            AdventBattleResultRankAnimationGaugeRate.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsAchievedRank()
        {
            return AfterTotalScore == TargetRankLowerRequiredScore;
        }

        public bool IsTargetMaxRankLevel()
        {
            return TargetRankType == AdventBattleConst.AdventBattleMaxRankType &&
                   TargetScoreRankLevel.IsMaxLevel() &&
                   IsAchievedRank();
        }
        
        public bool IsScoreUpdated()
        {
            return BeforeTotalScore != AfterTotalScore;
        }
    }
}