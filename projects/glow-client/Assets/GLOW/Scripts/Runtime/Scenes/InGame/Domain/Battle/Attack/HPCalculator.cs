using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class HPCalculator : IHPCalculator
    {
        record DamageCalculationResult(
            Damage Damage,
            HP UpdatedHp,
            IReadOnlyList<IStateEffectModel> UpdatedStateEffects,
            IReadOnlyList<HPCalculatorResultDetailModel> ResultDetails,
            SurvivedByGutsFlag IsSurvivedByGuts);

        record HealCalculationResult(
            Heal Heal,
            HP UpdatedHp,
            IReadOnlyList<HPCalculatorResultDetailModel> ResultDetails);
        
        // ダメージ計算対象のAttackDamageType（Healとかを除く）
        static readonly AttackDamageType[] DamageTypes = new[]
        {
            AttackDamageType.PoisonDamage,
            AttackDamageType.BurnDamage,
            AttackDamageType.SlipDamage,
            AttackDamageType.Damage,
            AttackDamageType.RushDamage,
        };

        [Inject] IStateEffectChecker StateEffectChecker { get; }

        public HPCalculatorResultModel CalculateHp(
            IReadOnlyList<HitAttackResultModel> attackResults,
            FieldObjectId targetId,
            CharacterColor targetColor,
            CharacterColorAdvantageDefenseBonus targetColorAdvantageDefenseBonus,
            HP currentHp,
            HP maxHp,
            IReadOnlyList<IStateEffectModel> stateEffects,
            DamageInvalidationFlag isDamageInvalidation,
            HealInvalidationFlag isHealInvalidation,
            UndeadFlag isUndead)
        {
            checked
            {
                var updatedHp = currentHp;
                var updatedStateEffects = stateEffects;
                var details = new List<HPCalculatorResultDetailModel>();

                var damageResult = CalculateTotalDamage(
                    attackResults,
                    targetId,
                    targetColor,
                    targetColorAdvantageDefenseBonus,
                    updatedHp,
                    maxHp,
                    updatedStateEffects,
                    isDamageInvalidation,
                    isUndead);

                updatedHp = damageResult.UpdatedHp;
                updatedStateEffects = damageResult.UpdatedStateEffects;
                details.AddRange(damageResult.ResultDetails);

                bool isOverKillDamage = currentHp <= damageResult.Damage;  // 不死状態でHP1で耐えても致死ダメージを受けた扱い

                var healResult = CalculateTotalHeal(
                    attackResults,
                    targetId,
                    updatedHp,
                    maxHp,
                    isHealInvalidation,
                    isOverKillDamage);

                updatedHp = healResult.UpdatedHp;
                details.AddRange(healResult.ResultDetails);

                return new HPCalculatorResultModel(
                    updatedHp,
                    damageResult.Damage,
                    healResult.Heal,
                    updatedStateEffects,
                    details,
                    damageResult.IsSurvivedByGuts);
            }
        }

        DamageCalculationResult CalculateTotalDamage(
            IReadOnlyList<HitAttackResultModel> attackResults,
            FieldObjectId targetId,
            CharacterColor targetColor,
            CharacterColorAdvantageDefenseBonus targetColorAdvantageDefenseBonus,
            HP currentHp,
            HP maxHp,
            IReadOnlyList<IStateEffectModel> stateEffects,
            DamageInvalidationFlag isDamageInvalidation,
            UndeadFlag isUndead)
        {
            var updatedStateEffects = stateEffects;
            var updatedHp = currentHp;

            var details = new List<HPCalculatorResultDetailModel>();
            var totalDamage = Damage.Zero;
            var isSurvivedByGuts = SurvivedByGutsFlag.False;

            foreach (var damageType in DamageTypes)
            {
                var result = CalculateDamage( 
                    damageType,
                    attackResults,
                    targetId,
                    targetColor,
                    targetColorAdvantageDefenseBonus,
                    updatedHp,
                    maxHp,
                    updatedStateEffects,
                    isDamageInvalidation,
                    isUndead);

                totalDamage += result.Damage;
                updatedHp = result.UpdatedHp;
                updatedStateEffects = result.UpdatedStateEffects;
                details.AddRange(result.ResultDetails);

                if (result.IsSurvivedByGuts)
                {
                    isSurvivedByGuts = SurvivedByGutsFlag.True;
                }
            }

            return new DamageCalculationResult(totalDamage, updatedHp, updatedStateEffects, details, isSurvivedByGuts);
        }

        DamageCalculationResult CalculateDamage(
            AttackDamageType calculationDamageType,
            IReadOnlyList<HitAttackResultModel> attackResults,
            FieldObjectId targetId,
            CharacterColor targetColor,
            CharacterColorAdvantageDefenseBonus targetColorAdvantageDefenseBonus,
            HP currentHp,
            HP maxHp,
            IReadOnlyList<IStateEffectModel> stateEffects,
            DamageInvalidationFlag isDamageInvalidation,
            UndeadFlag isUndead)
        {
            var updatedStateEffects = stateEffects;
            var updatedHp = currentHp;

            var details = new List<HPCalculatorResultDetailModel>();
            var totalDamage = Damage.Zero;
            var isSurvivedByGuts = SurvivedByGutsFlag.False;

            foreach (var attackResult in attackResults)
            {
                if (attackResult.TargetId != targetId) continue;
                if (attackResult.AttackDamageType != calculationDamageType) continue;

                // 攻撃側の色属性有利時は倍率ボーナス
                var isAdvantageColor = attackResult.IsAdvantageColor(targetColor);
                var advantageAttackBonus = isAdvantageColor
                    ? attackResult.CharacterColorAdvantageAttackBonus
                    : CharacterColorAdvantageAttackBonus.Default;

                var power = AttackPowerCalculator.CalculateAttackPower(
                    attackResult.BasePower,
                    attackResult.PowerParameter,
                    attackResult.BuffPercentages,
                    attackResult.DebuffPercentages,
                    advantageAttackBonus,
                    maxHp);

                var isKiller = attackResult.IsKiller(targetColor);
                var killerPercentage = isKiller ? attackResult.KillerPercentage : KillerPercentage.Hundred;

                power *= killerPercentage.ToPercentageM();

                // 氷結によるダメージ増加
                if (calculationDamageType == AttackDamageType.Damage)
                {
                    var damageIncreaseByFreezeResult =
                        StateEffectChecker.CheckAndReduceCount(StateEffectType.Freeze, updatedStateEffects);
                    updatedStateEffects = damageIncreaseByFreezeResult.UpdatedStateEffects;

                    if (damageIncreaseByFreezeResult.IsEffectActivated)
                    {
                        foreach (var parameter in damageIncreaseByFreezeResult.Parameters)
                        {
                            power *= parameter.ToPercentageM();
                        }
                    }
                }

                // 弱体化によるダメージ増加
                if (calculationDamageType == AttackDamageType.Damage || calculationDamageType == AttackDamageType.RushDamage)
                {
                    var damageIncreaseByWeakeningResult =
                        StateEffectChecker.CheckAndReduceCount(StateEffectType.Weakening, updatedStateEffects);
                    updatedStateEffects = damageIncreaseByWeakeningResult.UpdatedStateEffects;

                    if (damageIncreaseByWeakeningResult.IsEffectActivated)
                    {
                        foreach (var parameter in damageIncreaseByWeakeningResult.Parameters)
                        {
                            // 弱体化のパラメータは「○％弱体化」の形で設定される
                            power *= new PercentageM(parameter.Value + 100);
                        }
                    }
                }

                // 状態変化によるダメージ軽減
                (power, updatedStateEffects) = CutDamageByStateEffect(calculationDamageType, power, updatedStateEffects); 

                // 防御側の色属性有利時は倍率軽減
                if (attackResult.IsDisAdvantageColor(targetColor) && !targetColorAdvantageDefenseBonus.IsEmpty())
                {
                    power *= targetColorAdvantageDefenseBonus;
                }

                var damage = isDamageInvalidation ? Damage.Zero : power.ToDamage();
                totalDamage += damage;

                var prevUpdatedHp = updatedHp;
                updatedHp -= damage;

                // 不死状態のときは、HP0になる場合もHP1残す
                if (isUndead && updatedHp.IsZero())
                {
                    updatedHp = HP.One;
                }

                if (updatedHp.IsZero())
                {
                    var gutsResult = StateEffectChecker.CheckAndReduceCount(StateEffectType.Guts, updatedStateEffects);
                    updatedStateEffects = gutsResult.UpdatedStateEffects;
                    // 根性で耐えた場合
                    if (gutsResult.IsEffectActivated)
                    {
                        updatedHp = HP.One;
                        isSurvivedByGuts = SurvivedByGutsFlag.True;
                    }
                }

                // 適用されたダメージ量
                var appliedDamage = (prevUpdatedHp - updatedHp).ToDamage();

                var detail = new HPCalculatorResultDetailModel(
                    attackResult,
                    damage,
                    Heal.Zero,
                    appliedDamage,
                    Heal.Zero,
                    prevUpdatedHp,
                    updatedHp,
                    new KillerAttackFlag(isKiller),
                    new AdvantageUnitColorFlag(isAdvantageColor));

                details.Add(detail);
            }

            return new DamageCalculationResult(totalDamage, updatedHp, updatedStateEffects, details, isSurvivedByGuts);
        }

        (AttackPower, IReadOnlyList<IStateEffectModel>) CutDamageByStateEffect(
            AttackDamageType damageType,
            AttackPower power,
            IReadOnlyList<IStateEffectModel> stateEffects)
        {
            var updatedPower = power;
            var updatedStateEffects = stateEffects;
            
            var stateEffectTypes = damageType.GetStateEffectTypesThatCutMe();
            
            foreach (var stateEffectType in stateEffectTypes)
            {
                if (stateEffectType == StateEffectType.None) continue;
                
                var stateEffectDamageCutResult = StateEffectChecker.CheckAndReduceCount(
                    stateEffectType, 
                    updatedStateEffects);
                
                updatedStateEffects = stateEffectDamageCutResult.UpdatedStateEffects;
                
                if (stateEffectDamageCutResult.IsEffectActivated)
                {
                    foreach (var parameter in stateEffectDamageCutResult.Parameters)
                    {
                        updatedPower *= parameter.ToPercentageM().ComplementSet();
                    }
                }
            }
            
            return (updatedPower, updatedStateEffects);
        }

        HealCalculationResult CalculateTotalHeal(
            IReadOnlyList<HitAttackResultModel> attackResults,
            FieldObjectId targetId,
            HP currentHp,
            HP maxHp,
            HealInvalidationFlag isHealInvalidation,
            bool isOverKillDamage)
        {
            var details = new List<HPCalculatorResultDetailModel>();
            var totalHeal = Heal.Zero;
            var updatedHp = currentHp;

            foreach (var attackResult in attackResults)
            {
                if (attackResult.TargetId != targetId) continue;
                if (attackResult.AttackDamageType != AttackDamageType.Heal) continue;

                var power = AttackPowerCalculator.CalculateAttackPower(
                    attackResult.BasePower,
                    attackResult.PowerParameter,
                    attackResult.BuffPercentages,
                    attackResult.DebuffPercentages,
                    maxHp);

                // 回復は固定値前提としてこのタイミングで回復量の図鑑効果補正をかける
                power *= attackResult.HealPower;

                var heal = isOverKillDamage || isHealInvalidation || updatedHp.IsZero() ? Heal.Zero : power.ToHeal();
                totalHeal += heal;

                var hpDiff = maxHp - updatedHp;
                var appliedHeal = Heal.Min(heal, hpDiff.ToHeal());

                var prevUpdatedHp = updatedHp;
                updatedHp += appliedHeal;

                var detail = new HPCalculatorResultDetailModel(
                    attackResult,
                    Damage.Zero,
                    heal,
                    Damage.Zero,
                    appliedHeal,
                    prevUpdatedHp,
                    updatedHp,
                    KillerAttackFlag.False,
                    AdvantageUnitColorFlag.False);

                details.Add(detail);
            }

            return new HealCalculationResult(totalHeal, updatedHp, details);
        }
    }
}
