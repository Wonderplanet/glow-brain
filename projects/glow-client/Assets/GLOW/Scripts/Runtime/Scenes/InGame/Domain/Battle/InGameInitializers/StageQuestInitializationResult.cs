using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record StageQuestInitializationResult(
        MstStageModel MstStage,
        MstAdventBattleModel MstAdventBattle,
        MstQuestModel MstQuestModel,
        SelectedStageModel SelectedStageModel,
        IMstInGameModel MstInGameModel,
        IReadOnlyList<MstInGameSpecialRuleModel> MstInGameSpecialRules,
        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> MstInGameSpecialRuleUnitStatusModels,
        IReadOnlyList<MstStageEndConditionModel> MstStageEndConditions)
    {
        public static StageQuestInitializationResult Empty { get; } = new StageQuestInitializationResult(
            MstStageModel.Empty,
            MstAdventBattleModel.Empty,
            MstQuestModel.Empty,
            SelectedStageModel.Empty,
            MstStageModel.Empty,
            new List<MstInGameSpecialRuleModel>(),
            new List<MstInGameSpecialRuleUnitStatusModel>(),
            new List<MstStageEndConditionModel>());
    }
}
