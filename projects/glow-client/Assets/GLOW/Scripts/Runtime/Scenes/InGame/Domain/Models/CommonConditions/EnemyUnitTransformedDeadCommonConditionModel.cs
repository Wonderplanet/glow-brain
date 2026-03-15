using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyUnitTransformedDeadCommonConditionModel(AutoPlayerSequenceElementId AutoPlayerSequenceElementId) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyUnitTransformDead;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.DeadUnits.Any(u =>
                u.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId &&
                u.Transformation.IsTransformed());
        }
    }
}