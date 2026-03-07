using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record PvpResultModel(
        PvpResultEvaluator.PvpResultType ResultType,
        PvpMaxDistanceRatio PlayerDistanceRatio,
        PvpMaxDistanceRatio OpponentDistanceRatio,
        PvpResultEvaluator.PvpFinishType FinishType)
    {
        public static PvpResultModel Empty { get; } = new PvpResultModel(
            PvpResultEvaluator.PvpResultType.Defeat,
            PvpMaxDistanceRatio.Empty,
            PvpMaxDistanceRatio.Empty,
            PvpResultEvaluator.PvpFinishType.OutPostHpZero);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
