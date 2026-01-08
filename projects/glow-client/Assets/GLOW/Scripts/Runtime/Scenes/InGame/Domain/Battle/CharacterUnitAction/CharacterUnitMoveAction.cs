using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitMoveAction : ICharacterUnitAction
    {
        const float BaseMoveDistance = 0.0001f;

        public UnitActionState ActionState => UnitActionState.Move;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => true;

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitMoveAction.Update");
            var myUnit = context.CharacterUnit;
            var sortedPlayerAttackTargetCandidates = context.SortedPlayerAttackTargetCandidates;
            var sortedEnemyAttackTargetCandidates = context.SortedEnemyAttackTargetCandidates;
            var komaDictionary = context.KomaDictionary;
            var mstPage = context.MstPage;
            var currentTickCount = context.StageTime.CurrentTickCount;
            var tickCount = context.TickCount;
            var coordinateConverter = context.CoordinateConverter;
            var stateEffectChecker = context.StateEffectChecker;
            var nearestTargetFinder = context.NearestTargetFinder;

            TickCount remainingAttackInterval = UpdateRemainingAttackInterval(myUnit, tickCount);

            // 変身のチェック
            if (MeetsCondition(context, context.CharacterUnit.Transformation.Condition))
            {
                var result = ReturnResult(
                    myUnit,
                    currentTickCount,
                    remainingAttackInterval,
                    new CharacterUnitTransformationReadyAction(
                        CharacterUnitTransformationReadyAction.InitialDuration,
                        UnitActionStartFlag.True),
                    myUnit.Pos,
                    myUnit.LocatedKoma,
                    myUnit.StateEffects,
                    myUnit.RemainingMoveLoopCount,
                    myUnit.IsMoveStopped);
                
                Profiler.EndSample();
                return result;
            }

            // 次の攻撃を取得
            var attackData = myUnit.NextAttackKind == AttackKind.Special
                ? myUnit.SpecialAttack
                : myUnit.NormalAttack;

            var attackTargetSelectionData = CreateAttackTargetSelectionData(myUnit, attackData, context);

            var nearestForOrTargetPos = nearestTargetFinder.GetNearestFoeOrTargetPos(
                myUnit,
                attackTargetSelectionData,
                sortedPlayerAttackTargetCandidates,
                sortedEnemyAttackTargetCandidates,
                coordinateConverter);

            // 間合いが相手前線か攻撃対象に到達するまでの距離を求める
            var betweenWellDistanceToFoeFrontLine =
                nearestForOrTargetPos - myUnit.Pos.X - myUnit.WellDistance.Value;

            // 間合いが相手前線か攻撃対象を越えてたら移動しない
            if (betweenWellDistanceToFoeFrontLine <= 0f)
            {
                var result = ReturnResult(
                    myUnit,
                    currentTickCount,
                    remainingAttackInterval,
                    new CharacterUnitEngageAction(),
                    myUnit.Pos,
                    myUnit.LocatedKoma,
                    myUnit.StateEffects,
                    myUnit.RemainingMoveLoopCount,
                    myUnit.IsMoveStopped);
                
                Profiler.EndSample();
                return result;
            }

            // 攻撃できる状態で攻撃対象が攻撃範囲内なら移動しない
            if (remainingAttackInterval.IsZero()
                && !attackData.IsEmpty()
                && IsTargetInAttackRange(attackTargetSelectionData, context))
            {
                var result = ReturnResult(
                    myUnit,
                    currentTickCount,
                    remainingAttackInterval,
                    new CharacterUnitEngageAction(),
                    myUnit.Pos,
                    myUnit.LocatedKoma,
                    myUnit.StateEffects,
                    myUnit.RemainingMoveLoopCount,
                    myUnit.IsMoveStopped);
                
                Profiler.EndSample();
                return result;
            }

            // 移動停止、再開のチェック
            var moveLoopCount = myUnit.RemainingMoveLoopCount;
            if (myUnit.IsMoveStopped)
            {
                // 再移動設定が無い or 停止条件が座標指定の場合移動せずそのまま or 再移動の行動条件未達成
                if (myUnit.MoveRestartCondition.ConditionType == InGameCommonConditionType.None ||
                    myUnit.MoveStopCondition.ConditionType == InGameCommonConditionType.EnemyUnitTargetPosition ||
                    !MeetsCondition(context, context.CharacterUnit.MoveRestartCondition))
                {
                    var moveResult = ReturnResult(
                        myUnit,
                        currentTickCount,
                        remainingAttackInterval,
                        UnitMoveActionFactory.Create(myUnit.IsMoveStarted),
                        myUnit.Pos,
                        myUnit.LocatedKoma,
                        myUnit.StateEffects,
                        myUnit.RemainingMoveLoopCount,
                        myUnit.IsMoveStopped);
                    
                    Profiler.EndSample();
                    return moveResult;
                }

                // 再移動の行動条件達成したらループカウントを減らし移動させる
                moveLoopCount -= 1;
            }
            else
            {
                // ループカウントが残っており、停止条件を満たしていたら停止時のコマ情報や時間を現在の状態に更新する
                if (myUnit.RemainingMoveLoopCount.IsLoopValid() 
                    && MeetsCondition(context, context.CharacterUnit.MoveStopCondition))
                {
                    // 停止時のコマ情報や時間を現在の状態に更新
                    var stopResult = ReturnResult(
                        myUnit,
                        currentTickCount,
                        remainingAttackInterval,
                        UnitMoveActionFactory.Create(myUnit.IsMoveStarted),
                        myUnit.Pos,
                        myUnit.LocatedKoma,
                        myUnit.StateEffects,
                        myUnit.RemainingMoveLoopCount,
                        MoveStoppedFlag.True);
                    
                    Profiler.EndSample();
                    return stopResult;
                }
            }

            // 移動量を求める
            var maxMoveDistance = GetMoveDistance(myUnit,
                tickCount,
                out var updatedEffects,
                stateEffectChecker);

            // 今回の移動で相手前線が間合いに入る場合、ちょうど間合いギリギリに入る位置まで移動
            var moveDistance = Mathf.Min(maxMoveDistance, betweenWellDistanceToFoeFrontLine);

            // 移動後の位置とコマ
            var newPos = OutpostCoordV2.Translate(myUnit.Pos, moveDistance, 0f);

            var locatedKomaId =
                mstPage.GetKomaIdAt(coordinateConverter.OutpostToFieldCoord(myUnit.BattleSide, newPos));
            var locatedKoma = komaDictionary.GetValueOrDefault(locatedKomaId, KomaModel.Empty);

            // 相手が間合いに入る場合はEngageActionに遷移
            ICharacterUnitAction nextAction = moveDistance < maxMoveDistance
                ? new CharacterUnitEngageAction()
                : UnitMoveActionFactory.Create(myUnit.IsMoveStarted);

            var finalResult = ReturnResult(
                myUnit,
                currentTickCount,
                remainingAttackInterval,
                nextAction,
                newPos,
                locatedKoma,
                updatedEffects,
                moveLoopCount,
                MoveStoppedFlag.False);
            
            Profiler.EndSample();
            return finalResult;
        }

        float GetMoveDistance(
            CharacterUnitModel characterUnit,
            TickCount tickCount,
            out IReadOnlyList<IStateEffectModel> updatedEffects,
            IStateEffectChecker stateEffectChecker)
        {
            updatedEffects = characterUnit.StateEffects;

            // 移動速度アップ／ダウン効果の反映
            var buffPercentage = PercentageM.Zero;
            var deBuffPercentage = PercentageM.Zero;

            var moveSpeedUpResult = stateEffectChecker.CheckAndReduceCount(StateEffectType.MoveSpeedUp, updatedEffects);
            updatedEffects = moveSpeedUpResult.UpdatedStateEffects;
            if (moveSpeedUpResult.IsEffectActivated)
            {
                buffPercentage += moveSpeedUpResult.Parameters
                    .Select(parameter => parameter.ToPercentageM())
                    .Sum();
            }

            var moveSpeedUpInNormalKomaResult =
                stateEffectChecker.CheckAndReduceCount(StateEffectType.MoveSpeedUpInNormalKoma, updatedEffects);
            updatedEffects = moveSpeedUpInNormalKomaResult.UpdatedStateEffects;
            if (moveSpeedUpInNormalKomaResult.IsEffectActivated)
            {
                buffPercentage += moveSpeedUpInNormalKomaResult.Parameters
                    .Select(parameter => parameter.ToPercentageM())
                    .Sum();
            }

            var moveSpeedDownResult = stateEffectChecker.CheckAndReduceCount(StateEffectType.MoveSpeedDown, updatedEffects);
            updatedEffects = moveSpeedDownResult.UpdatedStateEffects;
            if (moveSpeedDownResult.IsEffectActivated)
            {
                deBuffPercentage = moveSpeedDownResult.Parameters
                    .Select(parameter => parameter.ToPercentageM())
                    .Sum();
            }

            if (characterUnit.UnitMoveSpeed.IsZero())
            {
                return 0f;
            }

            var percentage = PercentageM.Hundred + buffPercentage - deBuffPercentage;
            var moveSpeed = UnitMoveSpeed.Max(characterUnit.UnitMoveSpeed * percentage, UnitMoveSpeed.LowerLimitWithDebuff);

            return moveSpeed.Value * tickCount.Value * BaseMoveDistance;
        }

        TickCount UpdateRemainingAttackInterval(CharacterUnitModel unitModel, TickCount tickCount)
        {
            if (unitModel.RemainingAttackInterval.IsZero())
            {
                return TickCount.Zero;
            }

            // プレイヤーが必殺ワザ使用を要求したら即発動
            if (unitModel.AttackComboCycle.IsEmpty() && unitModel.NextAttackKind == AttackKind.Special)
            {
                return TickCount.Zero;
            }

            return unitModel.RemainingAttackInterval - tickCount;
        }

        AttackTargetSelectionData CreateAttackTargetSelectionData(
            CharacterUnitModel characterUnit,
            AttackData attackData,
            CharacterUnitActionContext context)
        {
            var mainAttackElement = attackData.MainAttackElement;
            if (mainAttackElement.IsEmpty()) return AttackTargetSelectionData.Empty;

            var fieldCoordRange = GetAttackFieldCoordRange(characterUnit, mainAttackElement, context);

            return new AttackTargetSelectionData(
                characterUnit.Id,
                characterUnit.BattleSide,
                mainAttackElement.AttackTarget,
                mainAttackElement.AttackTargetType,
                mainAttackElement.TargetColors,
                mainAttackElement.TargetRoles,
                mainAttackElement.AttackDamageType == AttackDamageType.Heal,
                mainAttackElement.MaxTargetCount,
                fieldCoordRange);
        }

        bool IsTargetInAttackRange(
            AttackTargetSelectionData attackTargetSelectionData,
            CharacterUnitActionContext context)
        {
            return AttackTargetSelector.IsTargetInAttackRange(
                context.SortedPlayerAttackTargetCandidates,
                context.SortedEnemyAttackTargetCandidates,
                attackTargetSelectionData,
                context.CoordinateConverter);
        }

        static CoordinateRange GetAttackFieldCoordRange(
            CharacterUnitModel characterUnit,
            AttackElement attackElement,
            CharacterUnitActionContext context)
        {
            if (attackElement.IsEmpty()) return CoordinateRange.Empty;

            return AttackRangeConverter.ToFieldCoordAttackRange(
                characterUnit.BattleSide,
                characterUnit.Pos,
                attackElement.AttackRange,
                context.MstPage,
                context.CoordinateConverter);
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
            TickCount currentTickCount,
            TickCount remainingAttackInterval,
            ICharacterUnitAction action,
            OutpostCoordV2 pos,
            KomaModel locatedKoma,
            IReadOnlyList<IStateEffectModel> effects,
            MoveLoopCount remainingMoveLoopCount,
            MoveStoppedFlag isMoveStopped)
        {
            var posUpdateStageTickCount = Mathf.Approximately(unit.Pos.X, pos.X) 
                ? unit.PosUpdateStageTickCount 
                : currentTickCount;
            
            // 移動停止したときは移動停止時の時間を更新
            var moveStopStageTickCount = unit.MoveStopStageTickCount;

            if (!unit.IsMoveStopped && isMoveStopped)
            {
                moveStopStageTickCount = currentTickCount;
            }
            
            // 移動開始時の時間とコマを更新
            var moveStartStageTickCount = unit.MoveStartStageTickCount;
            var moveStartedKoma = unit.MoveStartedKoma;
            
            if (unit.IsMoveStopped && !isMoveStopped)
            {
                moveStartStageTickCount = currentTickCount;
                moveStartedKoma = locatedKoma;
            }
            
            // CharacterUnitModelの更新
            var updatedUnit = unit with
            {
                RemainingAttackInterval = remainingAttackInterval,
                Action = action,
                PrevActionState = unit.Action.ActionState,
                Pos = pos,
                PrevPos = unit.Pos,
                LocatedKoma = locatedKoma,
                PrevLocatedKoma = unit.LocatedKoma,
                MoveStartedKoma = moveStartedKoma,
                PosUpdateStageTickCount = posUpdateStageTickCount,
                MoveStopStageTickCount = moveStopStageTickCount,
                MoveStartStageTickCount = moveStartStageTickCount,
                StateEffects = effects,
                RemainingMoveLoopCount = remainingMoveLoopCount,
                IsMoveStopped = isMoveStopped,
                IsPrevMoveStopped = unit.IsMoveStopped,
            };

            return (updatedUnit, Array.Empty<IAttackModel>());
        }
    }
}
