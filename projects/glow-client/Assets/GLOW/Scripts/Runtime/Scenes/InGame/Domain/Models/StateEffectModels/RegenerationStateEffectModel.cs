using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record RegenerationStateEffectModel(
        StateEffectId Id,
        StateEffectSourceId SourceId,
        StateEffectType Type,
        EffectiveCount EffectiveCount,
        EffectiveProbability EffectiveProbability,
        TickCount Duration,
        TickCount HealInterval,
        TickCount RemainingHealInterval,
        StateEffectParameter Parameter,
        IStateEffectConditionModel Condition,
        GeneratedFirstAttackFlag IsGeneratedFirstAttack, // 継続回復は付与時の次回フレームで即時回復を行うため初回生成ずみかの管理フラグ
        bool NeedsDisplay) : IStateEffectModel
    {
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

            var remainingHealInterval = RemainingHealInterval.IsZero()
                ? HealInterval
                : RemainingHealInterval - tickCount;

            return this with
            {
                Duration = duration,
                RemainingHealInterval = remainingHealInterval
            };
        }

        public AttackData GenerateAttack()
        {
            if (!RemainingHealInterval.IsZero() && IsGeneratedFirstAttack)
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
                Array.Empty<MasterDataId>(),
                Array.Empty<MasterDataId>(),
                AttackDamageType.Heal,
                AttackHitData.NoNockBack,
                AttackHitStopFlag.False,
                Percentage.Hundred,
                Type == StateEffectType.RegenerationByFixed
                    ? Parameter.ToFixedAttackPowerParameter()
                    : Parameter.ToMaxHpPercentageAttackPowerParameter(),
                StateEffect.Empty,
                Array.Empty<AttackSubElement>());

            return new AttackData(
                TickCount.Zero,
                attackBaseData,
                new[] { attackElement });
        }
    }
}
