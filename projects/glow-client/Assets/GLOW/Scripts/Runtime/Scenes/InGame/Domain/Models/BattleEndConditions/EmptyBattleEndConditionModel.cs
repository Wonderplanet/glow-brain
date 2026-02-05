using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record EmptyBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public static EmptyBattleEndConditionModel Instance { get; } = new(StageEndType.Finish);

        public StageEndConditionType StageEndConditionType => StageEndConditionType.None;

        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return false;
        }
    }
}
