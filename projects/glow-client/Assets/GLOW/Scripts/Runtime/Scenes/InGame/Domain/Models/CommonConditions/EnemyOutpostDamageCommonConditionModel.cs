using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyOutpostDamageCommonConditionModel(HP Damage) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyOutpostDamage;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var enemyOutpost = context.EnemyOutpost;

            HP diff = enemyOutpost.MaxHp - enemyOutpost.Hp;
            return diff >= Damage;
        }
    }
}
