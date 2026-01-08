using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyOutpostHpPercentageCommonConditionModel(PercentageM HpPercentage) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyOutpostHpPercentage;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var enemyOutpost = context.EnemyOutpost;

            var conditionHp = enemyOutpost.MaxHp * HpPercentage;
            return enemyOutpost.Hp <= conditionHp;
        }
    }
}
