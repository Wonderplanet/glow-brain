using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record PvpResultViewModel(
        PvpResultEvaluator.PvpResultType ResultType,
        PvpMaxDistanceRatio PlayerDistanceRatio,
        PvpMaxDistanceRatio OpponentDistanceRatio,
        PvpResultEvaluator.PvpFinishType FinishType)
    {
        public static PvpResultViewModel Empty { get; } = new (
            PvpResultEvaluator.PvpResultType.Defeat,
            PvpMaxDistanceRatio.Empty,
            PvpMaxDistanceRatio.Empty,
            PvpResultEvaluator.PvpFinishType.OutPostHpZero);
    }
}