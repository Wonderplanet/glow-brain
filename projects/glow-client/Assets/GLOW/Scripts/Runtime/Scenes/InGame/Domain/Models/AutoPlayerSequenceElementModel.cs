using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AutoPlayerSequenceElementModel(
        AutoPlayerSequenceElementId Id,
        AutoPlayerSequenceGroupId SequenceGroupId,
        AutoPlayerSequenceElementId PrioritySequenceElementId,
        SequenceCondition ActivationCondition,
        SequenceCondition DeactivationCondition,
        AutoPlayerSequenceAction Action,
        SummonAnimationType SummonAnimationType,
        AutoPlayerSequenceSummonCount SummonCount,
        FieldCoordV2 SummonPosition,
        MoveStartConditionType MoveStartConditionType,
        MoveStartConditionValue MoveStartConditionValue,
        MoveStopConditionType MoveStopConditionType,
        MoveStopConditionValue MoveStopConditionValue,
        MoveStartConditionType MoveRestartConditionType,
        MoveStartConditionValue MoveRestartConditionValue,
        MoveLoopCount MoveLoopCount,
        TickCount SummonInterval,
        TickCount ActionDelay,
        DropBattlePoint OverrideDropBattlePoint,
        float EnemyHpCoef,
        float EnemyAttackCoef,
        float EnemySpeedCoef,
        OutpostDamageInvalidationFlag IsSummonUnitOutpostDamageInvalidation)
    {
        public static AutoPlayerSequenceElementModel Empty { get; } = new(
            AutoPlayerSequenceElementId.Empty,
            AutoPlayerSequenceGroupId.Empty,
            AutoPlayerSequenceElementId.Empty,
            SequenceCondition.Empty,
            SequenceCondition.Empty,
            AutoPlayerSequenceAction.Empty,
            SummonAnimationType.None,
            AutoPlayerSequenceSummonCount.Empty,
            FieldCoordV2.Empty,
            MoveStartConditionType.None,
            MoveStartConditionValue.Empty,
            MoveStopConditionType.None,
            MoveStopConditionValue.Empty,
            MoveStartConditionType.None,
            MoveStartConditionValue.Empty,
            MoveLoopCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            DropBattlePoint.Empty,
            0,
            0,
            0,
            OutpostDamageInvalidationFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public FieldCoordV2 GetResetPositionForContinue()
        {
            // Continue時に特別処理が必要なConditionTypeか判断
            if (ActivationCondition.Type == AutoPlayerSequenceConditionType.InitialSummon ||
                ActivationCondition.Type == AutoPlayerSequenceConditionType.DarknessKomaCleared)
            {
                return SummonPosition.IsEmpty()
                    ? FieldCoordV2.Empty
                    : SummonPosition;
            }

            return FieldCoordV2.Empty;
        }
    }
}
