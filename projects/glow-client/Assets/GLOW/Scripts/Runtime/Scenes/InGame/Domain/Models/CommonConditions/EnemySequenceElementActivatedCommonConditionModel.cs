using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemySequenceElementActivatedCommonConditionModel(AutoPlayerSequenceElementId AutoPlayerSequenceElementId) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemySequenceElementActivated;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.CurrentSequenceGroupModel.SequenceElementStateModels
                .Exists(e => e.ElementModel.SequenceElementId == AutoPlayerSequenceElementId && e.IsActivated);
        }
    }
}
