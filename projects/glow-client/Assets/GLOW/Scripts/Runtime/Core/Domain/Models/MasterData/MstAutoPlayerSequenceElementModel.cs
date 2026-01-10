using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstAutoPlayerSequenceElementModel(
        AutoPlayerSequenceSetId SequenceSetId,
        AutoPlayerSequenceElementId SequenceElementId,
        AutoPlayerSequenceGroupId SequenceGroupId,
        AutoPlayerSequenceElementId PrioritySequenceElementId,
        SequenceCondition ActivationCondition,
        SequenceCondition DeactivationCondition,
        AutoPlayerSequenceAction Action,
        SummonAnimationType SummonAnimationType,
        AutoPlayerSequenceSummonCount SummonCount,
        TickCount SummonInterval,
        TickCount ActionDelay,
        FieldCoordV2 SummonPosition,
        MoveStartConditionType MoveStartConditionType,
        MoveStartConditionValue MoveStartConditionValue,
        MoveStopConditionType MoveStopConditionType,
        MoveStopConditionValue MoveStopConditionValue,
        MoveStartConditionType MoveRestartConditionType,
        MoveStartConditionValue MoveRestartConditionValue,
        MoveLoopCount MoveLoopCount,
        UnitAuraType AuraType,
        UnitDeathType DeathType,
        DropBattlePoint OverrideDropBattlePoint,
        InGameScore DefeatedScore,
        AutoPlayerSequenceCoefficient EnemyHpCoef,
        AutoPlayerSequenceCoefficient EnemyAttackCoef,
        AutoPlayerSequenceCoefficient EnemySpeedCoef,
        OutpostDamageInvalidationFlag IsSummonUnitOutpostDamageInvalidation)
    {
        public static MstAutoPlayerSequenceElementModel Empty { get; } = new(
            AutoPlayerSequenceSetId.Empty,
            AutoPlayerSequenceElementId.Empty,
            AutoPlayerSequenceGroupId.Empty,
            AutoPlayerSequenceElementId.Empty,
            SequenceCondition.Empty,
            SequenceCondition.Empty,
            AutoPlayerSequenceAction.Empty,
            SummonAnimationType.None,
            AutoPlayerSequenceSummonCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            FieldCoordV2.Empty,
            MoveStartConditionType.None,
            MoveStartConditionValue.Empty,
            MoveStopConditionType.None,
            MoveStopConditionValue.Empty,
            MoveStartConditionType.None,
            MoveStartConditionValue.Empty,
            MoveLoopCount.Empty,
            UnitAuraType.Default,
            UnitDeathType.Normal,
            DropBattlePoint.Empty,
            InGameScore.Empty,
            AutoPlayerSequenceCoefficient.Empty,
            AutoPlayerSequenceCoefficient.Empty,
            AutoPlayerSequenceCoefficient.Empty,
            OutpostDamageInvalidationFlag.False
        );

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
