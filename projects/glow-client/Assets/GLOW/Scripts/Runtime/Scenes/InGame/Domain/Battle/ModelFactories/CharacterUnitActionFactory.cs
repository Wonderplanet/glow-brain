using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class CharacterUnitActionFactory : ICharacterUnitActionFactory
    {
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }

        public ICharacterUnitAction CreateAttackHitAction(
            AttackHitData attackHitData,
            AttackHitData attachHitDataForNextHitAction,
            CharacterUnitModel unit,
            StageTimeModel stageTime)
        {
            var prevAction = !attachHitDataForNextHitAction.IsEmpty()
                ? CreateAttackHitAction(attachHitDataForNextHitAction, unit.Action, unit, stageTime)
                : unit.Action;

            return CreateAttackHitAction(attackHitData, prevAction, unit, stageTime);
        }

        ICharacterUnitAction CreateAttackHitAction(
            AttackHitData attackHitData,
            ICharacterUnitAction prevAction,
            CharacterUnitModel unit,
            StageTimeModel stageTime)
        {
            return attackHitData.HitType switch
            {
                AttackHitType.AccumulatedDamageKnockBack => new CharacterUnitKnockBackAction(
                    new TickCount(20),
                    AdjustKnockBackDistance(unit, 0.25f),
                    prevAction),

                AttackHitType.KnockBack1 => new CharacterUnitKnockBackAction(
                    new TickCount(20),
                    AdjustKnockBackDistance(unit, 0.25f),
                    prevAction),

                AttackHitType.KnockBack2 => new CharacterUnitKnockBackAction(
                    new TickCount(30),
                    AdjustKnockBackDistance(unit, 0.375f),
                    prevAction),

                AttackHitType.KnockBack3 => new CharacterUnitKnockBackAction(
                    new TickCount(40),
                    AdjustKnockBackDistance(unit, 0.5f),
                    prevAction),

                AttackHitType.ForcedKnockBack1 => new CharacterUnitForceKnockBackAction(
                    new TickCount(20),
                    AdjustKnockBackDistance(unit, 0.25f),
                    prevAction),

                AttackHitType.ForcedKnockBack2 => new CharacterUnitForceKnockBackAction(
                    new TickCount(30),
                    AdjustKnockBackDistance(unit, 0.375f),
                    prevAction),

                AttackHitType.ForcedKnockBack3 => new CharacterUnitForceKnockBackAction(
                    new TickCount(40),
                    AdjustKnockBackDistance(unit, 0.5f),
                    prevAction),

                AttackHitType.ForcedKnockBack5 => new CharacterUnitForceKnockBackAction(
                    InGameConstants.BossAppearanceKnockBackFrames,
                    AdjustKnockBackDistance(unit, 0.75f),
                    prevAction),

                AttackHitType.Stun => new CharacterUnitStunAction(
                    stageTime.CurrentTickCount,
                    attackHitData.HitParameter1.ToTickCount(),
                    StateEffectSourceIdProvider.GenerateNewId(),
                    UnitActionStartFlag.True),

                AttackHitType.Freeze => new CharacterUnitFreezeAction(
                    stageTime.CurrentTickCount,
                    attackHitData.HitParameter1.ToTickCount(),
                    StateEffectSourceIdProvider.GenerateNewId(),
                    UnitActionStartFlag.True),
                _ => unit.Action
            };
        }

        /// <summary>
        /// ゲートより後ろにノックバクしないようにする
        /// </summary>
        float AdjustKnockBackDistance(CharacterUnitModel unit, float distance)
        {
            // ノックバックでキャラの位置がマイナスにならないようにする -> 位置0がゲート位置なのでゲートより後ろにノックバックしない
            return Mathf.Min(unit.Pos.X, distance);
        }
    }
}
