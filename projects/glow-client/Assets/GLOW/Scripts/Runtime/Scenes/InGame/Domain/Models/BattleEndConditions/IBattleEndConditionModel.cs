using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.BattleEndConditions
{
    public interface IBattleEndConditionModel
    {
        StageEndType StageEndType { get; }

        StageEndConditionType StageEndConditionType { get; }

        bool MeetsCondition(BattleEndConditionContext context);
    }
}
