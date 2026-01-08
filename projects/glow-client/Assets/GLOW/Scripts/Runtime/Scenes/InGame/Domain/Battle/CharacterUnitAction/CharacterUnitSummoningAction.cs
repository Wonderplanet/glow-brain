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
    public class CharacterUnitSummoningAction : ICharacterUnitAction
    {
        readonly TickCount _remainingTime;

        public UnitActionState ActionState => UnitActionState.Summoning;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.True;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.True;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState == UnitActionState.Restart;

        public CharacterUnitSummoningAction(TickCount remainingTime)
        {
            _remainingTime = remainingTime;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitSummoningAction.Update");
            var characterUnit = context.CharacterUnit;
            var tickCount = context.TickCount;
            var stageTime = context.StageTime;

            var updatedRemainingTime = _remainingTime - tickCount;

            if (updatedRemainingTime.IsZero())
            {
                var nextAttackKind = !characterUnit.AttackComboCycle.IsEmpty()
                    ? characterUnit.GetNextNextComboAttackKind()
                    : characterUnit.NextAttackKind;

                ICharacterUnitAction action = characterUnit.AppearanceAttack.IsEmpty()
                    ? new CharacterUnitPrevMoveAction()
                    : new CharacterUnitAppearanceAttackAction(TickCount.Zero);

                Profiler.EndSample();
                return ReturnResult(characterUnit, nextAttackKind, action, stageTime.CurrentTickCount);
            }

            Profiler.EndSample();
            return ReturnResult(
                characterUnit,
                characterUnit.NextAttackKind,
                new CharacterUnitSummoningAction(updatedRemainingTime),
                TickCount.Empty );
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            AttackKind nextAttackKind,
            ICharacterUnitAction action,
            TickCount summonedTickCount)
        {
            var updatedCharacterUnit = characterUnit with
            {
                NextAttackKind = nextAttackKind,
                Action = action,
                PrevActionState = characterUnit.Action.ActionState,
                // PrevLocatedKomaId = characterUnit.LocatedKomaId,
                SummonedTickCount = summonedTickCount
            };

            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
