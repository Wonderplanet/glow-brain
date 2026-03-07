using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public record GiveUpBattleEndConditionModel(StageEndType StageEndType) : IBattleEndConditionModel
    {
        public StageEndConditionType StageEndConditionType => StageEndConditionType.GiveUp;

        // GiveUpがtrueかどうか
        public bool MeetsCondition(BattleEndConditionContext context)
        {
            return context.IsGiveUp;
        }
    }
}
