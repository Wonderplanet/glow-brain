using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaEffectDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Domain.UseCases
{
    public class GetEncyclopediaEffectUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }

        public EncyclopediaEffectDialogUseCaseModel GetEffects()
        {
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var mstEffects = MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffects();
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var grade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(userUnits);
            var effectHp = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaUnitEffectValue(
                mstRewards,
                mstEffects,
                grade,
                UnitEncyclopediaEffectType.Hp);
            var effectAtk = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaUnitEffectValue(
                mstRewards,
                mstEffects,
                grade,
                UnitEncyclopediaEffectType.AttackPower);
            var effectHeal = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaUnitEffectValue(
                mstRewards,
                mstEffects,
                grade,
                UnitEncyclopediaEffectType.Heal);


            return new EncyclopediaEffectDialogUseCaseModel(
                effectAtk,
                effectHp,
                effectHeal);
        }
    }
}
