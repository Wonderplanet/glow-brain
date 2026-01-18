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
    public class CharacterUnitInterruptSlideAction : ICharacterUnitAction
    {
        readonly TickCount _duration;
        readonly float _distance;
        readonly ICharacterUnitAction _interruptedAction;

        public UnitActionState ActionState => UnitActionState.InterruptSlide;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => true;

        public CharacterUnitInterruptSlideAction(TickCount duration, float distance, ICharacterUnitAction interruptedAction)
        {
            _duration = duration;
            _distance = distance;
            _interruptedAction = interruptedAction;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitInterruptSlideAction.Update");
            CharacterUnitModel unit = context.CharacterUnit;
            var komaDictionary = context.KomaDictionary;
            var mstPage = context.MstPage;
            var coordinateConverter = context.CoordinateConverter;
            var currentTickCount = context.StageTime.CurrentTickCount;
            var tickCount = context.TickCount;

            if (_duration.IsZero())
            {
                Profiler.EndSample();
                return ReturnResult(
                    unit,
                    _interruptedAction,
                    unit.Pos,
                    unit.LocatedKoma,
                    unit.PosUpdateStageTickCount);
            }

            float moveDistance = _distance / _duration * tickCount;
            float remainingDistance = _distance - moveDistance;

            OutpostCoordV2 newPos = OutpostCoordV2.Translate(unit.Pos, -moveDistance, 0f);
            
            // 自陣営のデフォルト出現位置を超えないように制限
            if (newPos.X < InGameConstants.DefaultSummonPos.X)
            {
                newPos = new OutpostCoordV2(InGameConstants.DefaultSummonPos.X, newPos.Y);
                remainingDistance = 0f;
            }

            var locatedKomaId =
                mstPage.GetKomaIdAt(coordinateConverter.OutpostToFieldCoord(unit.BattleSide, newPos));
            var locatedKoma = komaDictionary.GetValueOrDefault(locatedKomaId, KomaModel.Empty);

            var remainingDuration = _duration - context.TickCount;

            ICharacterUnitAction nextAction = remainingDuration.IsZero()
                ? _interruptedAction
                : new CharacterUnitInterruptSlideAction(remainingDuration, remainingDistance, _interruptedAction);

            Profiler.EndSample();
            return ReturnResult(
                unit,
                nextAction,
                newPos,
                locatedKoma,
                currentTickCount);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            ICharacterUnitAction action,
            OutpostCoordV2 pos,
            KomaModel locatedKoma,
            TickCount posUpdateStageTickCount)
        {
            var updatedCharacterUnit = characterUnit with
            {
                Action = action,
                PrevActionState = characterUnit.Action.ActionState,
                Pos = pos,
                PrevPos = characterUnit.Pos,
                LocatedKoma = locatedKoma,
                PrevLocatedKoma = characterUnit.LocatedKoma,
                PosUpdateStageTickCount = posUpdateStageTickCount,
            };


            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
