using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record TimeOverBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.TimeOver;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.StageTime.IsTimeLimitOver;
        }
    }
}
