using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Evaluator
{
    public interface IInGameRetryEvaluator
    {
        InGameRetryModel DetermineRetryAvailableFlag();
    }
}