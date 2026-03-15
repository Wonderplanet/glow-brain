using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyUnitDeadCommonConditionModel(
        AutoPlayerSequenceElementId AutoPlayerSequenceElementId) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyUnitDead;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.DeadUnits.Any(unit =>
                unit.BattleSide == BattleSide.Enemy && unit.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId);
        }
    }
}
