using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record DeadEnemyUnitCountCommonConditionModel(DefeatEnemyCount Count) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.DeadEnemyUnitCount;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.TotalDeadEnemyCount >= Count;
        }
    }
}
