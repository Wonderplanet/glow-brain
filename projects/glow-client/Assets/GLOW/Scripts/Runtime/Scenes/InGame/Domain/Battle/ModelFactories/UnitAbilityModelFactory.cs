using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class UnitAbilityModelFactory : IUnitAbilityModelFactory
    {
        [Inject] ICommonConditionModelFactory CommonConditionModelFactory { get; }
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }
        
        public UnitAbilityModel Create(UnitAbility ability)
        {
            var commonConditionModel = CreateCommonConditionModel(ability);
            
            return new UnitAbilityModel(
                ability.Type,
                ability.Parameter1,
                ability.Parameter2,
                ability.Parameter3,
                StateEffectSourceIdProvider.GenerateNewId(),
                commonConditionModel);
        }

        ICommonConditionModel CreateCommonConditionModel(UnitAbility ability)
        {
            switch (ability.Type)
            {
                case UnitAbilityType.DamageCutByHpPercentageLess:
                case UnitAbilityType.AttackPowerUpByHpPercentageLess:
                    return CommonConditionModelFactory.Create(
                        InGameCommonConditionType.MyHpLessThanOrEqualPercentage,
                        ability.Parameter2.ToCommonConditionValue());
                
                case UnitAbilityType.DamageCutByHpPercentageOver:
                case UnitAbilityType.AttackPowerUpByHpPercentageOver:
                    return CommonConditionModelFactory.Create(
                        InGameCommonConditionType.MyHpMoreThanOrEqualPercentage,
                        ability.Parameter2.ToCommonConditionValue());
                
                // 常に発動するものとかは別の
                default:
                    return EmptyCommonConditionModel.Instance; 
            }
        }
    }
}