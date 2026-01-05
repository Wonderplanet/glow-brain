using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitAbilityModel(
        UnitAbilityType Type,
        UnitAbilityParameter Parameter1,
        UnitAbilityParameter Parameter2,
        UnitAbilityParameter Parameter3,
        StateEffectSourceId StateEffectSourceId,
        ICommonConditionModel CommonConditionModel)
    {
        static readonly List<UnitAbilityType> UnitAbilityThatAriseInNormalKoma = new()
        {
            UnitAbilityType.AttackPowerUpInNormalKoma,
            UnitAbilityType.MoveSpeedUpInNormalKoma,
            UnitAbilityType.DamageCutInNormalKoma,
        };

        static readonly Dictionary<UnitAbilityType, KomaEffectType> TargetKomaEffectDictionary = new()
        {
            { UnitAbilityType.SlipDamageKomaBlock, KomaEffectType.SlipDamage },
            { UnitAbilityType.AttackPowerDownKomaBlock, KomaEffectType.AttackPowerDown },
            { UnitAbilityType.GustKomaBlock, KomaEffectType.Gust },
            { UnitAbilityType.AttackPowerUpKomaBoost, KomaEffectType.AttackPowerUp },
        };

        /// <summary>
        /// 召喚直後に状態変化を付与される特性一覧(以降常に発動する)
        /// </summary>
        static readonly List<UnitAbilityType> UnitAbilityThatAriseOnceOnSummon = new()
        {
            UnitAbilityType.KnockBackBlock,
            UnitAbilityType.Guts,
            UnitAbilityType.PoisonDamageCut,
            UnitAbilityType.BurnDamageCut,
            UnitAbilityType.StunBlock,
            UnitAbilityType.FreezeBlock,
            UnitAbilityType.WeakeningBlock,
        };

        public static UnitAbilityModel Empty { get; } = new(
            UnitAbilityType.None,
            UnitAbilityParameter.Empty,
            UnitAbilityParameter.Empty,
            UnitAbilityParameter.Empty,
            StateEffectSourceId.Empty,
            EmptyCommonConditionModel.Instance);

        /// <summary>
        /// コマに関係なく常時発生する特性かどうか(召喚以降常に発動する特性かどうか)
        /// </summary>
        /// <returns></returns>
        public bool ArisesStateEffectOnceOnSummon => UnitAbilityThatAriseOnceOnSummon.Contains(Type);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool ArisesStateEffectIn(KomaModel koma)
        {
            if (koma.IsEmpty()) return false;

            // 通常コマで発動する特性の場合
            if (koma.IsNormalKoma())
            {
                return UnitAbilityThatAriseInNormalKoma.Contains(Type);
            }

            // 特定のコマ効果のコマで発動する特性の場合
            if (!TargetKomaEffectDictionary.ContainsKey(Type)) return false;

            var targetKomaEffect = TargetKomaEffectDictionary[Type];

            return koma.KomaEffects.Any(komaEffect => komaEffect.EffectType == targetKomaEffect);
        }
        
        public bool ArisesStateEffectConditionAchieved(ICommonConditionContext context)
        {
            // Parameter2に条件となる閾値が入る想定。設定がない場合は発動しない
            return CommonConditionModel.MeetsCondition(context);
        }

        public StateEffect GetStateEffect()
        {
            return Type switch
            {
                UnitAbilityType.SlipDamageKomaBlock => CreateStateEffect(StateEffectType.SlipDamageKomaBlock),
                UnitAbilityType.AttackPowerDownKomaBlock => CreateStateEffect(StateEffectType.AttackPowerDownKomaBlock),
                UnitAbilityType.GustKomaBlock => CreateStateEffect(StateEffectType.GustKomaBlock),
                UnitAbilityType.AttackPowerUpKomaBoost => CreateStateEffect(StateEffectType.AttackPowerUpKomaBoost, Parameter1),
                UnitAbilityType.AttackPowerUpInNormalKoma => CreateStateEffect(StateEffectType.AttackPowerUpInNormalKoma, Parameter1),
                UnitAbilityType.MoveSpeedUpInNormalKoma => CreateStateEffect(StateEffectType.MoveSpeedUpInNormalKoma, Parameter1),
                UnitAbilityType.DamageCutInNormalKoma => CreateStateEffect(StateEffectType.DamageCutInNormalKoma, Parameter1),
                UnitAbilityType.KnockBackBlock => CreateStateEffect(StateEffectType.KnockBackBlock),

                UnitAbilityType.Guts => CreateStateEffect(
                    StateEffectType.Guts,
                    Parameter1.ToEffectiveCount(),
                    Parameter2.ToEffectiveProbability(),
                    TickCount.Infinity,
                    StateEffectParameter.Empty,
                    StateEffectConditionValue.Empty,
                    StateEffectConditionValue.Empty),

                UnitAbilityType.StunBlock => CreateStateEffect(
                    StateEffectType.StunBlock,
                    EffectiveCount.Infinity,
                    Parameter1.ToEffectiveProbability(),
                    TickCount.Infinity,
                    StateEffectParameter.Empty,
                    Parameter2.ToStateEffectConditionValue(),
                    Parameter3.ToStateEffectConditionValue()),

                UnitAbilityType.FreezeBlock => CreateStateEffect(
                    StateEffectType.FreezeBlock,
                    EffectiveCount.Infinity,
                    Parameter1.ToEffectiveProbability(),
                    TickCount.Infinity,
                    StateEffectParameter.Empty,
                    Parameter2.ToStateEffectConditionValue(),
                    Parameter3.ToStateEffectConditionValue()),

                UnitAbilityType.WeakeningBlock => CreateStateEffect(
                    StateEffectType.WeakeningBlock,
                    EffectiveCount.Infinity,
                    EffectiveProbability.Hundred,   // 弱体化無効化は確率設定なし
                    TickCount.Infinity,
                    StateEffectParameter.Empty,
                    Parameter2.ToStateEffectConditionValue(),
                    Parameter3.ToStateEffectConditionValue()),

                UnitAbilityType.PoisonDamageCut => CreateStateEffect(StateEffectType.PoisonDamageCut, Parameter1),
                UnitAbilityType.BurnDamageCut => CreateStateEffect(StateEffectType.BurnDamageCut, Parameter1),
                
                UnitAbilityType.AttackPowerUpByHpPercentageOver => CreateStateEffect(StateEffectType.AttackPowerUpByHpPercentage, Parameter1),
                UnitAbilityType.AttackPowerUpByHpPercentageLess => CreateStateEffect(StateEffectType.AttackPowerUpByHpPercentage, Parameter1),
                UnitAbilityType.DamageCutByHpPercentageOver => CreateStateEffect(StateEffectType.DamageCutByHpPercentage, Parameter1),
                UnitAbilityType.DamageCutByHpPercentageLess => CreateStateEffect(StateEffectType.DamageCutByHpPercentage, Parameter1),
                _ => StateEffect.Empty
            };
        }

        StateEffect CreateStateEffect(StateEffectType type)
        {
            return new StateEffect(
                type,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                StateEffectParameter.Empty,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }

        StateEffect CreateStateEffect(StateEffectType type, UnitAbilityParameter parameter)
        {
            return new StateEffect(
                type,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                parameter.ToStateEffectParameter(),
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }

        StateEffect CreateStateEffect(StateEffectType type,
            EffectiveCount effectiveCount,
            EffectiveProbability effectiveProbability,
            TickCount duration,
            StateEffectParameter stateEffectParameter,
            StateEffectConditionValue condition1,
            StateEffectConditionValue condition2)
        {
            return new StateEffect(
                type,
                effectiveCount,
                effectiveProbability,
                duration,
                stateEffectParameter,
                condition1,
                condition2);
        }
    }
}
