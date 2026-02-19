using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record DefenseTargetBreakDownBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.DefenseTargetBreakDown;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.DefenseTargetHP.IsZero();
        }
    }
}
