using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record MyDamageCommonConditionModel(HP Damage) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.MyDamage;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var myUnit = context.MyUnit;
            if (myUnit.IsEmpty()) return false;

            HP diff = myUnit.MaxHp - myUnit.Hp;
            return diff >= Damage;
        }
    }
}
