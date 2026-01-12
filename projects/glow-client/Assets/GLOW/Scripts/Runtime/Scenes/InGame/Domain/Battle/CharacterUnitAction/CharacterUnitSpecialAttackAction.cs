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
    public class CharacterUnitSpecialAttackAction : ICharacterUnitAction
    {
        readonly TickCount _elapsedAttackTickCount = TickCount.Empty;

        public UnitActionState ActionState => UnitActionState.SpecialAttack;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public TickCount ElapsedAttackTickCount => _elapsedAttackTickCount;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState != UnitActionState.KnockBack;

        public CharacterUnitSpecialAttackAction(TickCount elapsedActionTickCount)
        {
            _elapsedAttackTickCount = elapsedActionTickCount;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitSpecialAttackAction.Update");
            CharacterUnitModel characterUnit = context.CharacterUnit;
            ICoordinateConverter coordinateConverter = context.CoordinateConverter;
            IAttackModelFactory attackModelFactory = context.AttackModelFactory;
            TickCount tickCount = context.TickCount;
            IBuffStatePercentageConverter buffStatePercentageConverter = context.BuffStatePercentageConverter;

            if (characterUnit.SpecialAttack.IsEmpty())
            {
                var result = ReturnResult(
                    characterUnit,
                    new CharacterUnitEngageAction(),
                    Array.Empty<IAttackModel>(),
                    characterUnit.StateEffects);
                Profiler.EndSample();
                return result;
            }

            var attacks = new List<IAttackModel>();
            IReadOnlyList<IStateEffectModel> updatedEffects = characterUnit.StateEffects;

            var updatedElapsedTime = _elapsedAttackTickCount + tickCount;

            var attackData = characterUnit.SpecialAttack;
            foreach (var attackElement in attackData.AttackElements)
            {
                var attackDelay = attackData.AttackDelay + attackElement.AttackDelay;

                if (attackDelay >= _elapsedAttackTickCount && attackDelay < updatedElapsedTime)
                {
                    (var attack, var updatedEffectsResult ) = attackModelFactory.Create(
                        characterUnit.Id,
                        characterUnit.CharacterId,
                        characterUnit.StateEffectSourceId,
                        characterUnit.BattleSide,
                        characterUnit.RoleType,
                        characterUnit.Color,
                        characterUnit.Pos,
                        characterUnit.AttackPower,
                        characterUnit.HealPower,
                        characterUnit.ColorAdvantageAttackBonus,
                        attackData.BaseData,
                        attackElement,
                        updatedEffects,
                        context.MstPage,
                        coordinateConverter,
                        buffStatePercentageConverter);

                    attacks.Add(attack);
                    updatedEffects = updatedEffectsResult;
                }
            }

            if (updatedElapsedTime > attackData.BaseData.ActionDuration)
            {
                var result = ReturnResult(
                    characterUnit,
                    new CharacterUnitEngageAction(),
                    attacks,
                    updatedEffects);
                Profiler.EndSample();
                return result;
            }

            var actionResult = ReturnResult(
                characterUnit,
                new CharacterUnitSpecialAttackAction(updatedElapsedTime),
                attacks,
                updatedEffects);
            Profiler.EndSample();
            return actionResult;
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            ICharacterUnitAction action,
            IReadOnlyList<IAttackModel> attacks,
            IReadOnlyList<IStateEffectModel> effects)
        {
            var updatedCharacterUnit = characterUnit with
            {
                Action = action,
                PrevActionState = characterUnit.Action.ActionState,
                PrevLocatedKoma = characterUnit.LocatedKoma,
                StateEffects = effects
            };

            return (updatedCharacterUnit, attacks);
        }
    }
}
