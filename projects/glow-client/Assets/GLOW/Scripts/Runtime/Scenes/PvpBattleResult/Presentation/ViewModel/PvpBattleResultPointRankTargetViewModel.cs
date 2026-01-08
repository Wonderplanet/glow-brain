using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.ViewModel
{
    public record PvpBattleResultPointRankTargetViewModel(
        PvpPoint BeforePoint,
        PvpPoint AfterPoint,
        PvpPoint TargetRankLowerRequiredPoint,
        PvpRankClassType TargetRankType,
        PvpRankLevel TargetScoreRankLevel,
        PvpBattleResultRankAnimationGaugeRate BeforeGaugeRate,
        PvpBattleResultRankAnimationGaugeRate AfterGaugeRate)
    {
        public static PvpBattleResultPointRankTargetViewModel Empty { get; } = new PvpBattleResultPointRankTargetViewModel(
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpBattleResultRankAnimationGaugeRate.Empty,
            PvpBattleResultRankAnimationGaugeRate.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsAchievedRank()
        {
            return AfterPoint >= TargetRankLowerRequiredPoint;
        }

        public bool IsTargetMaxRankLevel()
        {
            return TargetRankType == PvpConst.PvpMaxRankClassType && TargetScoreRankLevel.IsMaxLevel();
        }

        public bool IsPointDecreased()
        {
            return AfterPoint < BeforePoint;
        }

        public bool IsPointUpdated()
        {
            return BeforePoint != AfterPoint;
        }
    }
}
