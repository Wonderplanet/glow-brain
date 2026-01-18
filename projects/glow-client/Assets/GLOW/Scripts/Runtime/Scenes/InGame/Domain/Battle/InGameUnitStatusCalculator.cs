using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameUnitStatusCalculator : IInGameUnitStatusCalculator
    {
        [Inject] IBuffStatePercentageConverter BuffStatePercentageConverter { get; }
        [Inject] IInGameEventBonusUnitEffectProvider InGameEventBonusUnitEffectProvider { get; }
        [Inject] IInGameSpecialRuleUnitStatusProvider InGameSpecialRuleUnitStatusProvider { get; }

        public AttackPower CalculateBuffAttackPower(AttackPower attackPower, IReadOnlyList<IStateEffectModel> buffs)
        {
            // 攻撃力計算
            var attackBuffPercentage =
                BuffStatePercentageConverter.GetAttackPowerBuffPercentages(buffs);
            var attackDebuffPercentage =
                BuffStatePercentageConverter.GetAttackPowerDebuffPercentages(buffs);

            var buffPercentage = PercentageM.Hundred + attackBuffPercentage.Sum() - attackDebuffPercentage.Sum();

            var resultAttackPower = AttackPower.Max(attackPower * buffPercentage, AttackPower.LowerLimitWithDebuff);
            return resultAttackPower;
        }

        public UnitMoveSpeed CalculateBuffUnitMoveSpeed(UnitMoveSpeed moveSpeed, IReadOnlyList<IStateEffectModel> buffs)
        {
            // 移動速度計算
            var speedBuffPercentage = BuffStatePercentageConverter.GetUnitMoveSpeedBuffPercentages(buffs);
            var speedDebuffPercentage = BuffStatePercentageConverter.GetUnitMoveSpeedDebuffPercentages(buffs);

            var buffPercentage = PercentageM.Hundred + speedBuffPercentage.Sum() - speedDebuffPercentage.Sum();

            var resultMoveSpeed = UnitMoveSpeed.Max(moveSpeed * buffPercentage, UnitMoveSpeed.LowerLimitWithDebuff);
            return resultMoveSpeed;
        }

        public UnitCalculateStatusModel CalculateStatus(
            UnitCalculateStatusModel calculatedStatus,
            MasterDataId unitId,
            IInGameUnitEncyclopediaEffectProvider unitEncyclopediaEffectProvider,
            InGameType inGameType,
            MasterDataId questId,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var eventBonus = GetEventBonus(
                unitId,
                inGameType,
                questId,
                eventBonusGroupId);
            var hpEncyclopediaEffect = unitEncyclopediaEffectProvider.GetHpEffectPercentage();
            var attackPowerEncyclopediaEffect = unitEncyclopediaEffectProvider.GetAttackPowerEffectPercentage();
            var specialRuleUnitStatusParameter = InGameSpecialRuleUnitStatusProvider
                .GetSpecialRuleUnitStatus(
                    unitId,
                    specialRuleUnitStatusModels);

            // HP計算は浮動小数点数で行い、最後に切り上げ
            var hpFloat = calculatedStatus.HP.Value *
                (float)hpEncyclopediaEffect.Value / 100f *
                (float)eventBonus.Value / 100f *
                (float)specialRuleUnitStatusParameter.HpParameter.Value / 100f;

            var hp = new HP((int)Math.Ceiling(hpFloat));

            var attackPower = calculatedStatus.AttackPower *
                              attackPowerEncyclopediaEffect *
                              eventBonus *
                              specialRuleUnitStatusParameter.AttackPowerParameter.ToRate();

            return new UnitCalculateStatusModel(hp, attackPower);
        }

        public UnitCalculateStatusModel CalculateStatusWithEncyclopediaEffect(
            UnitCalculateStatusModel calculatedStatus,
            IInGameUnitEncyclopediaEffectProvider unitEncyclopediaEffectProvider)
        {
            // 図鑑効果のみ適用
            var hpEncyclopediaEffect = unitEncyclopediaEffectProvider.GetHpEffectPercentage();
            var attackPowerEncyclopediaEffect = unitEncyclopediaEffectProvider.GetAttackPowerEffectPercentage();

            // HP計算は浮動小数点数で行い、最後に切り上げ
            var hpFloat = calculatedStatus.HP.Value *
                (float)hpEncyclopediaEffect.Value / 100f;

            var hp = new HP((int)Math.Ceiling(hpFloat));

            var attackPower = calculatedStatus.AttackPower *
                              attackPowerEncyclopediaEffect;

            return new UnitCalculateStatusModel(hp, attackPower);
        }

        PercentageM GetEventBonus(
            MasterDataId characterId,
            InGameType inGameType,
            MasterDataId questId,
            EventBonusGroupId eventBonusGroupId)
        {
            if (inGameType == InGameType.AdventBattle)
            {
                return InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                    characterId,
                    eventBonusGroupId);
            }
            else
            {
                return InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                    characterId,
                    questId);
            }
        }
    }
}
