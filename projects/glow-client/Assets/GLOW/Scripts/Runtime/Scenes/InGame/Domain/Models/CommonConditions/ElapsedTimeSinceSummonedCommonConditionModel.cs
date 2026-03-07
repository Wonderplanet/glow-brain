using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record ElapsedTimeSinceSummonedCommonConditionModel(TickCount ElapsedTickCount) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.ElapsedTimeSinceSummoned;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var currentElapsedTickCount = context.StageTime.CurrentTickCount - context.MyUnit.SummonedTickCount;
            return currentElapsedTickCount >= ElapsedTickCount;
        }
    }
}
