using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitAppearanceAttackAction : ICharacterUnitAction
    {
        readonly TickCount _elapsedAttackTickCount = TickCount.Empty;

        public UnitActionState ActionState => UnitActionState.AppearanceAttack;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.True;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public TickCount ElapsedAttackTickCount => _elapsedAttackTickCount;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState == UnitActionState.Restart;

        public CharacterUnitAppearanceAttackAction(TickCount elapsedActionTickCount)
        {
            _elapsedAttackTickCount = elapsedActionTickCount;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitAppearanceAttackAction.Update");
            CharacterUnitModel characterUnit = context.CharacterUnit;
            ICoordinateConverter coordinateConverter = context.CoordinateConverter;
            IAttackModelFactory attackModelFactory = context.AttackModelFactory;
            TickCount tickCount = context.TickCount;
            IBuffStatePercentageConverter buffStatePercentageConverter = context.BuffStatePercentageConverter;

            IReadOnlyList<IStateEffectModel> updatedEffects = characterUnit.StateEffects;
            var attacks = new List<IAttackModel>();

            var updatedElapsedTime = _elapsedAttackTickCount + tickCount;

            var attackData = characterUnit.AppearanceAttack;
            foreach (var attackElement in attackData.AttackElements)
            {
                var attackDelay = attackData.AttackDelay + attackElement.AttackDelay;

                if (attackDelay >= _elapsedAttackTickCount && attackDelay < updatedElapsedTime)
                {
                    (var attack, var updatedEffectsResult) = attackModelFactory.Create(
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
                Profiler.EndSample();
                return ReturnResult(
                    characterUnit,
                    new CharacterUnitPrevMoveAction(),
                    attacks,
                    updatedEffects);
            }

            Profiler.EndSample();
            return ReturnResult(
                characterUnit,
                new CharacterUnitAppearanceAttackAction(updatedElapsedTime),
                attacks,
                updatedEffects);
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
                StateEffects = effects,
                PrevLocatedKoma = characterUnit.LocatedKoma,
            };

            return (updatedCharacterUnit, attacks);
        }
    }
}
