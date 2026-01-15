using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyUnitSummonedCommonConditionModel(AutoPlayerSequenceElementId AutoPlayerSequenceElementId) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyUnitSummoned;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.Units.Any(unit =>
                unit.BattleSide == BattleSide.Enemy
                && unit.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId);
        }
    }
}
