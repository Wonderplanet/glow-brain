using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IAcquiredPlayerResourceModelFactory
    {
        List<PlayerResourceModel> CreateAcquiredPlayerResourcesForAdventBattle(
            AdventBattleEndResultModel battleResultModel,
            MstAdventBattleModel mstAdventBattleModel);

        List<PlayerResourceModel> CreateAcquiredPlayerResources(
            StageEndResultModel stageEndResultModel,
            MstStageModel mstStage);

        List<List<PlayerResourceModel>> CreateAcquiredPlayerResourcesGroupedByStaminaRap(
            StageEndResultModel stageEndResultModel,
            MstStageModel mstStage);
    }
}
