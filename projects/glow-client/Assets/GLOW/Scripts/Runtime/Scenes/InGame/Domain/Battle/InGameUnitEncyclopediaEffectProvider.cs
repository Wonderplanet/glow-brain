using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameUnitEncyclopediaEffectProvider : IInGameUnitEncyclopediaEffectProvider
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }

        [Inject]
        void OnInject()
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var grade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(userUnits);
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var mstEffects = MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffects();

            var hp = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.Hp);
            var attackPower = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.AttackPower);
            var heal = UnitEncyclopediaEffectCalculator
                .CalculateUnitEncyclopediaUnitEffectValue(mstRewards, mstEffects, grade, UnitEncyclopediaEffectType.Heal);

            _encyclopediaEffectModel = new InGameUnitEncyclopediaEffectModel(
                hp.ToPercentageM() + PercentageM.Hundred,
                attackPower.ToPercentageM() + PercentageM.Hundred,
                heal.ToPercentageM() + PercentageM.Hundred);
        }

        InGameUnitEncyclopediaEffectModel _encyclopediaEffectModel;

        public PercentageM GetHpEffectPercentage()
        {
            return _encyclopediaEffectModel.HpEffectRate;
        }

        public PercentageM GetAttackPowerEffectPercentage()
        {
            return _encyclopediaEffectModel.AttackPowerEffectRate;
        }

        public PercentageM GetHealEffectPercentage()
        {
            return _encyclopediaEffectModel.HealEffectRate;
        }
    }
}
