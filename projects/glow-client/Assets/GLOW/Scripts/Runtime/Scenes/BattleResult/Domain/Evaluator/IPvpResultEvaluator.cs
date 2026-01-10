using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Evaluator
{
    public interface IPvpResultEvaluator
    {
        PvpResultModel Evaluate();
    }
}
