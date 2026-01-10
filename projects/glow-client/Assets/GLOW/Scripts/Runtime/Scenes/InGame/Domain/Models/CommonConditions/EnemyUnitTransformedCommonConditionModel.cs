using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyUnitTransformedCommonConditionModel(AutoPlayerSequenceElementId AutoPlayerSequenceElementId) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyUnitTransformed;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.Units.Any(u =>
                u.AutoPlayerSequenceElementId == AutoPlayerSequenceElementId &&
                u.Transformation.IsTransformed());
        }
    }
}
