using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitEngageAction : ICharacterUnitAction
    {
        // 相手前線がキャラの間合いよりもこの値以上離れていたら移動に移る
        const float BetweenWellDistanceToFoeFrontLineThreshold = 0.01f;

        public UnitActionState ActionState => UnitActionState.Engage;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => true;

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitEngageAction.Update");
            var myUnit = context.CharacterUnit;
            var sortedPlayerAttackTargetCandidates = context.SortedPlayerAttackTargetCandidates;
            var sortedEnemyAttackTargetCandidates = context.SortedEnemyAttackTargetCandidates;
            var tickCount = context.TickCount;
            var coordinateConverter = context.CoordinateConverter;
            var stateEffectChecker = context.StateEffectChecker;
            var nearestTargetFinder = context.NearestTargetFinder;

            var remainingAttackInterval = UpdateRemainingAttackInterval(myUnit, tickCount);

            var updatedEffects = myUnit.StateEffects;

            // 変身のチェック
            if (MeetsTransformationCondition(context))
            {
                Profiler.EndSample();
                return ReturnResult(
                    myUnit,
                    myUnit.AttackComboCount,
                    remainingAttackInterval,
                    myUnit.NextAttackKind,
                    new CharacterUnitTransformationReadyAction(
                        CharacterUnitTransformationReadyAction.InitialDuration,
                        UnitActionStartFlag.True),
                    updatedEffects);
            }

            // 次の攻撃を取得
            var attackData = myUnit.NextAttackKind == AttackKind.Special
                ? myUnit.SpecialAttack
                : myUnit.NormalAttack;

            var attackTargetSelectionData = CreateAttackTargetSelectionData(myUnit, attackData, context);

            // 攻撃範囲内に対象がいたらAttackChargeActionかAttackActionに移る
            if (!attackData.IsEmpty() && IsTargetInAttackRange(attackTargetSelectionData, context))
            {
                if (remainingAttackInterval.IsZero())
                {
                    // 次攻撃までの間隔を求める
                    var (intervalForNextAttack, effects) =
                        GetAttackIntervalForNextAttack(attackData.BaseData.AttackInterval, updatedEffects, stateEffectChecker);

                    var nextComboCount = !myUnit.AttackComboCycle.IsEmpty()
                        ? myUnit.AttackComboCount.NextComboCount(myUnit.AttackComboCycle)
                        : AttackComboCount.Zero;

                    var nextAttackKind = !myUnit.AttackComboCycle.IsEmpty()
                        ? myUnit.GetNextNextComboAttackKind()
                        : AttackKind.Normal;

                    // 通常攻撃はAttackAction、必殺ワザはChargeActionに移る
                    ICharacterUnitAction nextAction = myUnit.NextAttackKind == AttackKind.Special
                        ? new CharacterUnitAttackChargeAction(
                            myUnit.NextAttackKind,
                            CharacterUnitAttackChargeAction.InitialChargeTime)
                        : new CharacterUnitAttackAction(TickCount.Zero);

                    Profiler.EndSample();
                    return ReturnResult(
                        myUnit,
                        nextComboCount,
                        intervalForNextAttack,
                        nextAttackKind,
                        nextAction,
                        effects);
                }
            }

            // 相手前線が間合いよりある程度だけ離れていたら移動に移る
            var nearestFoePos = nearestTargetFinder.GetNearestFoeOrTargetPos(
                myUnit,
                attackTargetSelectionData,
                sortedPlayerAttackTargetCandidates,
                sortedEnemyAttackTargetCandidates,
                coordinateConverter);

            var betweenWellDistanceToFoeFrontLine = nearestFoePos - myUnit.Pos.X - myUnit.WellDistance.Value;

            if (betweenWellDistanceToFoeFrontLine > BetweenWellDistanceToFoeFrontLineThreshold)
            {
                Profiler.EndSample();
                return ReturnResult(
                    myUnit,
                    myUnit.AttackComboCount,
                    remainingAttackInterval,
                    myUnit.NextAttackKind,
                    UnitMoveActionFactory.Create(myUnit.IsMoveStarted),
                    updatedEffects);
            }

            Profiler.EndSample();
            return ReturnResult(
                myUnit,
                myUnit.AttackComboCount,
                remainingAttackInterval,
                myUnit.NextAttackKind,
                new CharacterUnitEngageAction(),
                updatedEffects);
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

        (TickCount, IReadOnlyList<IStateEffectModel>) GetAttackIntervalForNextAttack(
            TickCount baseInterval,
            IReadOnlyList<IStateEffectModel> effects,
            IStateEffectChecker stateEffectChecker)
        {
            var attackSpeedDownResult = stateEffectChecker.CheckAndReduceCount(StateEffectType.AttackSpeedDown, effects);
            effects = attackSpeedDownResult.UpdatedStateEffects;
            // 攻撃スピードダウン効果の反映
            if (attackSpeedDownResult.IsEffectActivated)
            {
                var rate = attackSpeedDownResult.Parameters
                    .Select(parameter => parameter.ToPercentageM().ComplementSet().ToInverseRate())
                    .Aggregate(1f, (a, b) => a * b);

                var interval = baseInterval * rate;

                return (interval, effects);
            }

            // 効果がない場合は設定値そのまま
            return (baseInterval, effects);
        }

        AttackTargetSelectionData CreateAttackTargetSelectionData(
            CharacterUnitModel characterUnit,
            AttackData attackData,
            CharacterUnitActionContext context)
        {
            var mainAttackElement = attackData.MainAttackElement;
            if (mainAttackElement.IsEmpty()) return AttackTargetSelectionData.Empty;

            var fieldCoordRange = AttackRangeConverter.ToFieldCoordAttackRange(
                characterUnit.BattleSide,
                characterUnit.Pos,
                mainAttackElement.AttackRange,
                context.MstPage,
                context.CoordinateConverter);

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


        bool MeetsTransformationCondition(CharacterUnitActionContext actionContext)
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

            return actionContext.CharacterUnit.Transformation.Condition.MeetsCondition(conditionContext);
        }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) ReturnResult(
            CharacterUnitModel characterUnit,
            AttackComboCount attackComboCount,
            TickCount remainingAttackInterval,
            AttackKind nextAttackKind,
            ICharacterUnitAction action,
            IReadOnlyList<IStateEffectModel> effects)
        {
            var updatedCharacterUnit = characterUnit with
            {
                AttackComboCount = attackComboCount,
                RemainingAttackInterval = remainingAttackInterval,
                NextAttackKind = nextAttackKind,
                Action = action,
                PrevActionState = characterUnit.Action.ActionState,
                PrevLocatedKoma = characterUnit.LocatedKoma,
                StateEffects = effects
            };

            return (updatedCharacterUnit, Array.Empty<IAttackModel>());
        }
    }
}
