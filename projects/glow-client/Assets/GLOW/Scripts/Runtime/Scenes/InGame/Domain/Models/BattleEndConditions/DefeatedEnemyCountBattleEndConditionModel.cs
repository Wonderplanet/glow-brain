using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record DefeatedEnemyCountBattleEndConditionModel(
        StageEndType StageEndType, DefeatEnemyCount DefeatedEnemyCount) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.DefeatedEnemyCount;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.DefeatEnemyCount >= DefeatedEnemyCount;
        }
    }
}
