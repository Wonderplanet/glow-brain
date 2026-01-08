using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitKnockBackActionBase
    {
        const float Speed = 0.0125f;

        readonly TickCount _duration;
        readonly float _distance;
        readonly ICharacterUnitAction _prevAction;

        public TickCount Duration => _duration;
        public float Distance => _distance;
        public ICharacterUnitAction PrevAction => _prevAction;

        public CharacterUnitKnockBackActionBase(TickCount duration, float distance, ICharacterUnitAction prevAction)
        {
            _duration = duration;
            _distance = distance;
            _prevAction = prevAction;
        }

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            CharacterUnitModel characterUnit = context.CharacterUnit;
            var komaDictionary = context.KomaDictionary;
            var mstPage = context.MstPage;
            var coordinateConverter = context.CoordinateConverter;
            var currentTickCount = context.StageTime.CurrentTickCount;
            var tickCount = context.TickCount;

            TickCount remainingAttackInterval = UpdateRemainingAttackInterval(
                characterUnit.RemainingAttackInterval, tickCount);

            var duration = TickCount.Min(context.TickCount, _duration);
            var remainingDuration = _duration - duration;

            float baseMoveDistance = Speed * duration.Value;
            float moveDistance = baseMoveDistance < _distance ? baseMoveDistance : _distance;
            float remainingDistance = _distance - moveDistance;

            OutpostCoordV2 newPos = OutpostCoordV2.Translate(characterUnit.Pos, -moveDistance, 0f);

            var locatedKomaId =
                mstPage.GetKomaIdAt(coordinateConverter.OutpostToFieldCoord(characterUnit.BattleSide, newPos));
            var locatedKoma = komaDictionary.GetValueOrDefault(locatedKomaId, KomaModel.Empty);

            var nextAction = CreateNextAction(remainingDuration, remainingDistance, _prevAction, characterUnit.IsMoveStarted);

            var posUpdateStageTickCount = newPos != characterUnit.Pos
                ? currentTickCount
                : characterUnit.PosUpdateStageTickCount;

            return ReturnResult(
                characterUnit,
                remainingAttackInterval,
                nextAction,
                newPos,
                locatedKoma,
                posUpdateStageTickCount);
        }

        protected virtual ICharacterUnitAction CreateKnockBackAction(
            TickCount remainingDuration,
            float remainingDistance,
            ICharacterUnitAction prevAction)
        {
            return null;
        }

        ICharacterUnitAction CreateNextAction(
            TickCount remainingDuration,
            float remainingDistance,
            ICharacterUnitAction prevAction,
            bool isMoveStarted)
        {
            if (!remainingDuration.IsZero())
            {
                return CreateKnockBackAction(remainingDuration, remainingDistance, prevAction);
            }

            if (prevAction.ActionState is UnitActionState.Stun or UnitActionState.Freeze)
            {
                return prevAction;
            }

            return UnitMoveActionFactory.Create(isMoveStarted);
        }

        TickCount UpdateRemainingAttackInterval(TickCount remainingAttackInterval, TickCount tickCount)
        {
            if (!remainingAttackInterval.IsZero())
            {
                return remainingAttackInterval - tickCount;
            }

            return remainingAttackInterval;
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            TickCount remainingAttackInterval,
            ICharacterUnitAction action,
            OutpostCoordV2 pos,
            KomaModel locatedKoma,
            TickCount posUpdateStageTickCount)
        {
            var updatedCharacterUnit = characterUnit with
            {
                RemainingAttackInterval = remainingAttackInterval,
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
