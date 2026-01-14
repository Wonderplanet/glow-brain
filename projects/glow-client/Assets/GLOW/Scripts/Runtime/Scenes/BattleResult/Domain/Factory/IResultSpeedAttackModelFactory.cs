using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IResultSpeedAttackModelFactory
    {
        public ResultSpeedAttackModel CreateSpeedAttackModel(
            GameFetchModel prevFetchModel,
            MstQuestModel mstQuest,
            MstStageModel mstStage,
            StageClearTime clearTime);
    }
}
