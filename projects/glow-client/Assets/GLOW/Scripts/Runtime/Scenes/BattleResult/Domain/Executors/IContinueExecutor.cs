using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Executors
{
    public interface IContinueExecutor
    {
        void Execute(MasterDataId selectedStageId);
    }
}
