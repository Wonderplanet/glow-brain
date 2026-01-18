using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IBattleEndConditionModelFactory
    {
        IBattleEndConditionModel Create(MstStageEndConditionModel mstModel);

        IBattleEndConditionModel Create(
            StageEndType stageEndType,
            StageEndConditionType conditionType,
            BattleEndConditionValue conditionValue1,
            BattleEndConditionValue conditionValue2);
    }
}
