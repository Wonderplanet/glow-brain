using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IPvpVictoryResultModelFactory
    {
        UniTask<VictoryResultModel> CreateVictoryPvpResultModel(
            CancellationToken cancellationToken,
            PvpResultEvaluator.PvpResultType resultType);
    }
}