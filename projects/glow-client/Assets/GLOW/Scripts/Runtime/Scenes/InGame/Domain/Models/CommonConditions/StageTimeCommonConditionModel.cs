using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record StageTimeCommonConditionModel(TickCount ElapsedTickCount) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.StageTime;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.StageTime.CurrentTickCount >= ElapsedTickCount;
        }
    }
}
