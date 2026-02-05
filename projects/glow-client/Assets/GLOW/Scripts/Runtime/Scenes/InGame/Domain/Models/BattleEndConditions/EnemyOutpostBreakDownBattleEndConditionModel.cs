using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record EnemyOutpostBreakDownBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.EnemyOutpostBreakDown;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.EnemyOutpostHP.IsZero();
        }
    }
}
