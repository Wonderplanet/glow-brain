using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record MyHpLessThanOrEqualPercentageCommonConditionModel(Percentage HpPercentage) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.MyHpLessThanOrEqualPercentage;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var myUnit = context.MyUnit;
            if (myUnit.IsEmpty()) return false;

            return myUnit.Hp <= myUnit.MaxHp * HpPercentage;
        }
    }
}
