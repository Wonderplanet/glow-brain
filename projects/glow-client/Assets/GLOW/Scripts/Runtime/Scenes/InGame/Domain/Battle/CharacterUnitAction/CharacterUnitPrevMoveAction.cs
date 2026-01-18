using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitPrevMoveAction : ICharacterUnitAction
    {
        public UnitActionState ActionState => UnitActionState.PrevMove;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => true;

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitPrevMoveAction.Update");
            CharacterUnitModel unit = context.CharacterUnit;
            var currentTickCount = context.StageTime.CurrentTickCount;

            // 変身のチェック
            if (MeetsCondition(context, unit.Transformation.Condition))
            {
                var nextAction = new CharacterUnitTransformationReadyAction(
                    CharacterUnitTransformationReadyAction.InitialDuration,
                    UnitActionStartFlag.True);
                
                Profiler.EndSample();
                return ReturnResult(unit, nextAction, currentTickCount);
            }

            // 行動条件達成したらMoveActionに移行
            ICharacterUnitAction action = UnitMoveActionFactory.Create(
                MeetsMoveStartCondition(context, unit.MoveStartCondition));

            Profiler.EndSample();
            return ReturnResult(unit, action, currentTickCount);
        }

        bool MeetsMoveStartCondition(CharacterUnitActionContext actionContext, ICommonConditionModel conditionModel)
        {
            if (conditionModel.ConditionType == InGameCommonConditionType.None) return true;

            return MeetsCondition(actionContext, conditionModel);
        }

        bool MeetsCondition(CharacterUnitActionContext actionContext, ICommonConditionModel conditionModel)
        {
            var conditionContext = new CommonConditionContext(
                actionContext.CharacterUnit,
                actionContext.CharacterUnits,
                actionContext.DeadUnits,
                actionContext.TotalDeadEnemyCount,
                actionContext.PlayerOutpost,
                actionContext.EnemyOutpost,
                actionContext.StageTime,
                actionContext.KomaDictionary,
                actionContext.MstPage,
                actionContext.EnemyCurrentSequenceGroupModel);

            return conditionModel.MeetsCondition(conditionContext);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel unit,
            ICharacterUnitAction action,
            TickCount currentTickCount)
        {
            var isMoveStarted = action.ActionState != UnitActionState.PrevMove;
            var isMoveStopped = !isMoveStarted ? MoveStoppedFlag.True : MoveStoppedFlag.False;
            var moveStartStageTickCount = isMoveStarted ? currentTickCount : unit.MoveStartStageTickCount;
            var moveStartedKoma = isMoveStarted ? unit.LocatedKoma : unit.MoveStartedKoma;
            
            var updatedUnit = unit with
            {
                Action = action,
                PrevActionState = unit.Action.ActionState,
                PrevLocatedKoma = unit.LocatedKoma,
                MoveStartedKoma = moveStartedKoma,
                MoveStartStageTickCount = moveStartStageTickCount,
                IsMoveStarted = isMoveStarted,
                IsMoveStopped = isMoveStopped,
                IsPrevMoveStopped = unit.IsMoveStopped
            };

            return (updatedUnit, Array.Empty<IAttackModel>());
        }
    }
}
