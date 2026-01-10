using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitStunAction : ICharacterUnitAction
    {
        readonly TickCount _startStateTime;
        readonly TickCount _duration;
        readonly StateEffectSourceId _unitEffectSourceId;
        readonly UnitActionStartFlag _isActionStart;

        public UnitActionState ActionState => UnitActionState.Stun;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public TickCount StartStateTime => _startStateTime;
        public TickCount Duration => _duration;
        public StateEffectSourceId UnitEffectSourceId => _unitEffectSourceId;
        public UnitActionStartFlag IsActionStart => _isActionStart;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState != UnitActionState.Stun;

        public CharacterUnitStunAction(
            TickCount startStateTime,
            TickCount duration,
            StateEffectSourceId unitEffectSourceId,
            UnitActionStartFlag isActionStart)
        {
            _startStateTime = startStateTime;
            _duration = duration;
            _unitEffectSourceId = unitEffectSourceId;
            _isActionStart = isActionStart;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitStunAction.Update");
            var myUnit = context.CharacterUnit;
            var stageTime = context.StageTime;
            var tickCount = context.TickCount;

            var remainingAttackInterval = myUnit.RemainingAttackInterval - tickCount;
            var updatedStateEffects = myUnit.StateEffects;

            // スタンの状態変化を付与
            if (_isActionStart)
            {
                var stunStateEffectModel = CreateStunStateEffectModel(
                    context.StateEffectModelFactory,
                    _unitEffectSourceId,
                    _duration);

                updatedStateEffects = updatedStateEffects.ToList().ChainAdd(stunStateEffectModel);
            }

            // Action終了時にスタンの状態変化を解除
            var elapsedTime = stageTime.CurrentTickCount - _startStateTime;
            var remainingDuration = _duration - elapsedTime;

            if (remainingDuration.IsZero())
            {
                updatedStateEffects = updatedStateEffects
                    .Where(effect => effect.SourceId != _unitEffectSourceId)
                    .ToList();
            }

            ICharacterUnitAction nextAction = CreateNextAction(remainingDuration, myUnit.IsMoveStarted);

            Profiler.EndSample();
            return ReturnResult(myUnit, remainingAttackInterval, nextAction, updatedStateEffects);
        }

        IStateEffectModel CreateStunStateEffectModel(
            IStateEffectModelFactory stateEffectModelFactory,
            StateEffectSourceId sourceId,
            TickCount duration)
        {
            var stateEffect = new StateEffect(
                StateEffectType.Stun,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                duration,
                StateEffectParameter.Empty,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty
            );

            return stateEffectModelFactory.Create(sourceId, stateEffect, true);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel unit,
            TickCount remainingAttackInterval,
            ICharacterUnitAction action,
            IReadOnlyList<IStateEffectModel> stateEffects)
        {
            var updatedUnit = unit with
            {
                RemainingAttackInterval = remainingAttackInterval,
                Action = action,
                PrevActionState = unit.Action.ActionState,
                PrevLocatedKoma = unit.LocatedKoma,
                StateEffects = stateEffects
            };

            return (updatedUnit, Array.Empty<IAttackModel>());
        }

        ICharacterUnitAction CreateNextAction(TickCount remainingDuration, bool isMoveStarted)
        {
            if (!remainingDuration.IsZero())
            {
                return new CharacterUnitStunAction(
                    _startStateTime,
                    _duration,
                    _unitEffectSourceId,
                    UnitActionStartFlag.False);
            }

            return UnitMoveActionFactory.Create(isMoveStarted);
        }
    }
}
