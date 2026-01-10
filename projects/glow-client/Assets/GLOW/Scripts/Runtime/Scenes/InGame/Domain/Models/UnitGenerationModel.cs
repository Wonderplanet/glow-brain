using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UnitGenerationModel(
        AutoPlayerSequenceElementId AutoPlayerSequenceElementId,
        InitialCharacterUnitCoef UnitCoef,
        MarchingLaneIdentifier MarchingLane,
        FieldCoordV2 SummonPosition,
        SummonAnimationType SummonAnimationType,
        ICommonConditionModel MoveStartCondition,
        ICommonConditionModel MoveStopCondition,
        ICommonConditionModel MoveRestartCondition,
        MoveLoopCount RemainingMoveLoopCount,
        UnitAuraType AuraType,
        UnitDeathType DeathType,
        FieldObjectId BeforeTransformationFieldObjectId,
        OutpostDamageInvalidationFlag IsOutpostDamageInvalidation,
        InGameScore DefeatedScore,
        DropBattlePoint OverrideDropBattlePoint,
        AppearanceAttackEnabledFlag IsAppearanceAttackEnabled)
    {
        public static UnitGenerationModel Empty { get; } = new(
            AutoPlayerSequenceElementId.Empty,
            InitialCharacterUnitCoef.Empty,
            MarchingLaneIdentifier.Empty,
            FieldCoordV2.Empty,
            SummonAnimationType.None,
            EmptyCommonConditionModel.Instance,
            EmptyCommonConditionModel.Instance,
            EmptyCommonConditionModel.Instance,
            MoveLoopCount.Empty,
            UnitAuraType.Default,
            UnitDeathType.Normal,
            FieldObjectId.Empty,
            OutpostDamageInvalidationFlag.False,
            InGameScore.Empty,
            DropBattlePoint.Empty,
            AppearanceAttackEnabledFlag.True);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
