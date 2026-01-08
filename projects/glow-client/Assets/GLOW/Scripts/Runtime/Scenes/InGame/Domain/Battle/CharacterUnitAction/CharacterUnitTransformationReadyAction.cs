using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitTransformationReadyAction : ICharacterUnitAction
    {
        public static TickCount InitialDuration = new TickCount(20);

        readonly TickCount _remainingDuration;
        readonly UnitActionStartFlag _isActionStart;

        public UnitActionState ActionState => UnitActionState.TransformationReady;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.True;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.True;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.True;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.True;

        public TickCount RemainingDuration => _remainingDuration;

        public bool CanForceChangeTo(UnitActionState actionState) =>
            actionState == UnitActionState.Restart || actionState == UnitActionState.InterruptSlide;

        public CharacterUnitTransformationReadyAction(TickCount remainingDuration, UnitActionStartFlag isActionStart)
        {
            _remainingDuration = remainingDuration;
            _isActionStart = isActionStart;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitTransformationReadyAction.Update");
            var unit = context.CharacterUnit;
            var tickCount = context.TickCount;

            var remainingDuration = _remainingDuration - tickCount;

            // 変身開始時に状態変化を全て解除する
            var updatedStateEffects = _isActionStart
                ? new List<IStateEffectModel>()
                : unit.StateEffects;

            ICharacterUnitAction updatedAction = remainingDuration.IsZero()
                ? new CharacterUnitTransformationAction()
                : new CharacterUnitTransformationReadyAction(remainingDuration, UnitActionStartFlag.False);

            Profiler.EndSample();
            return ReturnResult(unit, updatedAction, updatedStateEffects);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel unit,
            ICharacterUnitAction action,
            IReadOnlyList<IStateEffectModel> effects)
        {
            var updatedCharacterUnit = unit with
            {
                Action = action,
                PrevActionState = unit.Action.ActionState,
                PrevLocatedKoma = unit.LocatedKoma,
                StateEffects = effects,
            };

            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
