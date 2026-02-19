using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record ElapsedTimeSinceEnemySequenceGroupActivatedCommonConditionModel(TickCount ElapsedTime) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.ElapsedTimeSinceEnemySequenceGroupActivated;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.StageTime.CurrentTickCount - context.CurrentSequenceGroupModel.ActiveStartTime >= ElapsedTime;
        }
    }
}