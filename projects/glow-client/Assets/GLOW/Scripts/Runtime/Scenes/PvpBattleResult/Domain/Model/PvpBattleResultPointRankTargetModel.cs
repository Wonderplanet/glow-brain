using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpBattleResult.Domain.Model
{
    public record PvpBattleResultPointRankTargetModel(
        PvpPoint BeforePoint,
        PvpPoint AfterPoint,
        PvpPoint TargetRankLowerRequiredPoint,
        PvpRankClassType TargetRankType,
        PvpRankLevel TargetScoreRankLevel,
        PvpPoint BeforeRankLowerRequiredPoint)
    {
        public static PvpBattleResultPointRankTargetModel Empty { get; } = new PvpBattleResultPointRankTargetModel(
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpPoint.Empty);
        
        public bool IsDown()
        {
            return BeforePoint > AfterPoint;
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}