using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IStageVictoryResultModelFactory
    {
        UniTask<VictoryResultModel> VictoryInStage(
            CancellationToken cancellationToken,
            MasterDataId mstStageId);
    }
}
