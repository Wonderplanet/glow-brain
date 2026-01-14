using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitAttackChargeAction : ICharacterUnitAction
    {
        public static TickCount InitialChargeTime => new TickCount(25);

        readonly AttackKind _attackKind;
        readonly TickCount _remainingChargeTime;

        public UnitActionState ActionState => UnitActionState.AttackCharge;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public AttackKind AttackKind => _attackKind;
        public TickCount RemainingChargeTime => _remainingChargeTime;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState != UnitActionState.KnockBack;

        public CharacterUnitAttackChargeAction(
            AttackKind attackKind,
            TickCount remainingChargeTime)
        {
            _attackKind = attackKind;
            _remainingChargeTime = remainingChargeTime;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitAttackChargeAction.Update");
            CharacterUnitModel characterUnit = context.CharacterUnit;
            TickCount tickCount = context.TickCount;

            TickCount remainingChargeTime = _remainingChargeTime - tickCount;

            if (remainingChargeTime.IsZero())
            {
                ICharacterUnitAction action = _attackKind == AttackKind.Special
                    ? new CharacterUnitPreSpecialAttackAction()
                    : new CharacterUnitAttackAction(TickCount.Zero);

                Profiler.EndSample();
                return ReturnResult(characterUnit, action);
            }

            Profiler.EndSample();
            return ReturnResult(
                characterUnit,
                new CharacterUnitAttackChargeAction(_attackKind, remainingChargeTime));
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            ICharacterUnitAction action)
        {
            var updatedCharacterUnit = characterUnit with
            {
                Action = action,
                PrevActionState = characterUnit.Action.ActionState,
                PrevLocatedKoma = characterUnit.LocatedKoma,
            };

            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
