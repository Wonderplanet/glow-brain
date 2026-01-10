using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public interface IBattleEndConditionFactory
    {
        List<BattleEndCondition> CreateBattleEndConditionsForStage(
            MstQuestModel mstQuestModel,
            IMstInGameModel mstInGameModel,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels);

        List<BattleEndCondition> CreateBattleEndConditionsForAdventBattle();
    }
}
