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
    public class CharacterUnitTransformationAction : ICharacterUnitAction
    {
        public UnitActionState ActionState => UnitActionState.Transformation;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.True;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.True;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.True;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.True;

        public bool CanForceChangeTo(UnitActionState actionState) =>
            actionState == UnitActionState.Restart || actionState == UnitActionState.InterruptSlide;

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitTransformationAction.Update");
            var unit = context.CharacterUnit;

            // 即、変身終了
            var updatedTransformation = unit.Transformation with
            {
                IsTransformationFinish = UnitTransformationFinishFlag.True
            };

            Profiler.EndSample();
            return ReturnResult(unit, updatedTransformation);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel unit,
            UnitTransformationModel transformation)
        {
            var updatedCharacterUnit = unit with
            {
                PrevActionState = unit.Action.ActionState,
                PrevLocatedKoma = unit.LocatedKoma,
                Transformation = transformation,
            };

            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
