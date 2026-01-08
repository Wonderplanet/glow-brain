using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BurnDamageStateEffectModel(
        StateEffectId Id,
        StateEffectSourceId SourceId,
        StateEffectType Type,
        EffectiveCount EffectiveCount,
        EffectiveProbability EffectiveProbability,
        TickCount Duration,
        TickCount DamageInterval,
        TickCount RemainingDamageInterval,
        StateEffectParameter Parameter,
        IStateEffectConditionModel Condition,
        bool NeedsDisplay) : IStateEffectModel
    {
        AttackPowerParameter _attackPowerParameter;

        public bool IsEmpty()
        {
            return false;
        }

        public IStateEffectModel WithDecreasedEffectiveCount()
        {
            return this with { EffectiveCount = EffectiveCount - 1 };
        }

        public IStateEffectModel WithDecreasedDuration(TickCount tickCount)
        {
            var duration = Duration.IsZero() || Duration.IsInfinity()
                ? Duration
                : Duration - tickCount;

            var remainingDamageInterval = RemainingDamageInterval.IsZero()
                ? DamageInterval
                : RemainingDamageInterval - tickCount;

            return this with
            {
                Duration = duration,
                RemainingDamageInterval = remainingDamageInterval
            };
        }

        public AttackData GenerateAttack()
        {
            if (!RemainingDamageInterval.IsZero())
            {
                return AttackData.Empty;
            }

            var attackBaseData = new AttackBaseData(
                Array.Empty<CharacterColor>(),
                KillerPercentage.Hundred,
                TickCount.Zero,
                TickCount.Zero);

            var attackElement = new AttackElement(
                MasterDataId.Empty,
                TickCount.Zero,
                TickCount.Zero,
                AttackType.Direct,
                AttackRange.Empty,
                new FieldObjectCount(1),
                AttackViewId.Empty,
                AttackTarget.Self,
                AttackTargetType.All,
                (CharacterColor[])Enum.GetValues(typeof(CharacterColor)),
                (CharacterUnitRoleType[])Enum.GetValues(typeof(CharacterUnitRoleType)),
                AttackDamageType.BurnDamage,
                AttackHitData.NoNockBack,
                AttackHitStopFlag.False,
                Percentage.Hundred,
                Parameter.ToFixedAttackPowerParameter(),
                StateEffect.Empty,
                Array.Empty<AttackSubElement>());

            return new AttackData(
                TickCount.Zero,
                attackBaseData,
                new[] { attackElement });
        }
    }
}
