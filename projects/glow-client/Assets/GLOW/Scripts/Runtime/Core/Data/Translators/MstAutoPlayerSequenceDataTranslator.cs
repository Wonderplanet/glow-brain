using System.Collections.Generic;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstAutoPlayerSequenceDataTranslator
    {
        public static MstAutoPlayerSequenceModel Translate(
            AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId,
            IReadOnlyList<MstAutoPlayerSequenceElementModel> models)
        {
            return new MstAutoPlayerSequenceModel(
                mstAutoPlayerSequenceSetId,
                models
            );
        }

        public static MstAutoPlayerSequenceElementModel TranslateToElement(MstAutoPlayerSequenceData data)
        {
            var groupId = string.IsNullOrEmpty(data.SequenceGroupId)
                ? AutoPlayerSequenceGroupId.Empty
                : new AutoPlayerSequenceGroupId(data.SequenceGroupId);
            var prioritySequenceElementId = string.IsNullOrEmpty(data.PrioritySequenceElementId)
                ? AutoPlayerSequenceElementId.Empty
                : new AutoPlayerSequenceElementId(data.PrioritySequenceElementId);
            var condition = CreateSequenceCondition(data.ConditionType, data.ConditionValue);
            var deactivationCondition = CreateSequenceCondition(data.DeactivationConditionType, data.DeactivationConditionValue);
            var action = new AutoPlayerSequenceAction(
                data.ActionType,
                new AutoPlayerSequenceActionValue(data.ActionValue),
                new AutoPlayerSequenceActionValue(data.ActionValue2));
            var summonCount = data.SummonCount < 0
                    ? AutoPlayerSequenceSummonCount.Infinity
                    : new AutoPlayerSequenceSummonCount(data.SummonCount);
            var summonPosition = data.SummonPosition <= 0f
                ? FieldCoordV2.Empty
                : new FieldCoordV2(data.SummonPosition, 0f);
            var overrideDropBattlePoint = data.OverrideDropBattlePoint.HasValue
                ? new DropBattlePoint(data.OverrideDropBattlePoint.Value)
                : DropBattlePoint.Empty;

            return new MstAutoPlayerSequenceElementModel(
                new AutoPlayerSequenceSetId(data.SequenceSetId),
                new AutoPlayerSequenceElementId(data.SequenceElementId),
                groupId,
                prioritySequenceElementId,
                condition,
                deactivationCondition,
                action,
                data.SummonAnimationType,
                summonCount,
                new TickCount(data.SummonInterval),
                new TickCount(data.ActionDelay),
                summonPosition,
                data.MoveStartConditionType,
                new MoveStartConditionValue(data.MoveStartConditionValue),
                data.MoveStopConditionType,
                new MoveStopConditionValue(data.MoveStopConditionValue),
                data.MoveRestartConditionType,
                new MoveStartConditionValue(data.MoveRestartConditionValue),
                new MoveLoopCount(data.MoveLoopCount),
                data.AuraType,
                data.DeathType,
                overrideDropBattlePoint,
                new InGameScore(data.DefeatedScore),
                new AutoPlayerSequenceCoefficient(data.EnemyHpCoef),
                new AutoPlayerSequenceCoefficient(data.EnemyAttackCoef),
                new AutoPlayerSequenceCoefficient(data.EnemySpeedCoef),
                new OutpostDamageInvalidationFlag(data.IsSummonUnitOutpostDamageInvalidation)
            );
        }

        static SequenceCondition CreateSequenceCondition(AutoPlayerSequenceConditionType conditionType, string conditionValue)
        {
            return new SequenceCondition(
                conditionType,
                string.IsNullOrEmpty(conditionValue)
                    ? AutoPlayerSequenceConditionValue.Empty
                    : new AutoPlayerSequenceConditionValue(conditionValue));

        }
    }
}
