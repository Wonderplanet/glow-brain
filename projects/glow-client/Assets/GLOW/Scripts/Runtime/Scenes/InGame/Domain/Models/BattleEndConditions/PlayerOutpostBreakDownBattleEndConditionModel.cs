using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record PlayerOutpostBreakDownBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.PlayerOutpostBreakDown;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.PlayerOutpostHP.IsZero();
        }
    }
}
