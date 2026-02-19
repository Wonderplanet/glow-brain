using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class UnitGenerationModelFactory : IUnitGenerationModelFactory
    {
        [Inject] ICommonConditionModelFactory CommonConditionModelFactory { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public UnitGenerationModel Create(
            MstAutoPlayerSequenceElementModel autoPlayerSequenceElementModel,
            BattleSide battleSide,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IInitialCharacterUnitCoefFactory initialCharacterUnitCoefFactory)
        {
            var unitCoef = initialCharacterUnitCoefFactory.GenerateInitialUnitCoef(
                autoPlayerSequenceElementModel.Action.Value.ToMasterDataId(),
                stageEnemyParameterCoef,
                autoPlayerSequenceElementModel.EnemyHpCoef,
                autoPlayerSequenceElementModel.EnemyAttackCoef,
                autoPlayerSequenceElementModel.EnemySpeedCoef);

            var moveStartCondition = CommonConditionModelFactory.Create(
                ToCommonConditionType(autoPlayerSequenceElementModel.MoveStartConditionType, battleSide),
                autoPlayerSequenceElementModel.MoveStartConditionValue.ToCommonConditionValue());

            var moveStopCondition = CommonConditionModelFactory.Create(
                ToCommonConditionType(autoPlayerSequenceElementModel.MoveStopConditionType, battleSide),
                autoPlayerSequenceElementModel.MoveStopConditionValue.ToCommonConditionValue());

            var moveRestartCondition = CommonConditionModelFactory.Create(
                ToCommonConditionType(autoPlayerSequenceElementModel.MoveRestartConditionType, battleSide),
                autoPlayerSequenceElementModel.MoveRestartConditionValue.ToCommonConditionValue());
            
            // 移動停止条件がある場合は、MoveLoopCountを最低でも1にする
            // MoveLoopCountが0以下だと最初の停止も行われないため
            var moveLoopCount = moveStopCondition.ConditionType != InGameCommonConditionType.None && 
                                autoPlayerSequenceElementModel.MoveLoopCount <= 0
                ? MoveLoopCount.One
                : autoPlayerSequenceElementModel.MoveLoopCount;
            
            // 初期配置の場合はAppearanceAttackをしない
            var activationConditionType = autoPlayerSequenceElementModel.ActivationCondition.Type;
            var isAppearanceAttackEnabled = activationConditionType == AutoPlayerSequenceConditionType.InitialSummon
                ? AppearanceAttackEnabledFlag.False
                : AppearanceAttackEnabledFlag.True;

            return new UnitGenerationModel(
                autoPlayerSequenceElementModel.SequenceElementId,
                unitCoef,
                MarchingLaneIdentifier.Empty,
                autoPlayerSequenceElementModel.SummonPosition,
                autoPlayerSequenceElementModel.SummonAnimationType,
                moveStartCondition,
                moveStopCondition,
                moveRestartCondition,
                moveLoopCount,
                autoPlayerSequenceElementModel.AuraType,
                autoPlayerSequenceElementModel.DeathType,
                FieldObjectId.Empty,
                autoPlayerSequenceElementModel.IsSummonUnitOutpostDamageInvalidation,
                autoPlayerSequenceElementModel.DefeatedScore,
                autoPlayerSequenceElementModel.OverrideDropBattlePoint,
                isAppearanceAttackEnabled);
        }

        public UnitGenerationModel CreateTransformationUnitGenerationModel(CharacterUnitModel unitModel)
        {
            return new UnitGenerationModel(
                unitModel.AutoPlayerSequenceElementId,
                unitModel.InitialCoef,
                unitModel.MarchingLane,
                CoordinateConverter.OutpostToFieldCoord(unitModel.BattleSide, unitModel.Pos),
                SummonAnimationType.None,
                AlwaysCommonConditionModel.Instance,
                unitModel.MoveStopCondition,
                unitModel.MoveRestartCondition,
                unitModel.RemainingMoveLoopCount,
                unitModel.AuraType,
                unitModel.DeathType,
                unitModel.Id,
                unitModel.IsOutpostDamageInvalidation,
                unitModel.DefeatedScore,
                unitModel.DropBattlePoint,
                AppearanceAttackEnabledFlag.True);
        }

        InGameCommonConditionType ToCommonConditionType(MoveStartConditionType moveStartConditionType, BattleSide battleSide)
        {
            return moveStartConditionType switch
            {
                MoveStartConditionType.ElapsedTime => InGameCommonConditionType.ElapsedTimeSinceMoveStopped,

                MoveStartConditionType.FoeEnterSameKoma =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.PlayerUnitEnterSameKoma
                        : InGameCommonConditionType.None,

                MoveStartConditionType.EnterTargetKoma =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.PlayerUnitEnterSpecificKoma
                        : InGameCommonConditionType.None,

                MoveStartConditionType.Damage => InGameCommonConditionType.MyDamage,

                MoveStartConditionType.DeadFriendUnitCount =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.DeadEnemyUnitCount
                        : InGameCommonConditionType.None,

                _ => InGameCommonConditionType.None
            };
        }

        InGameCommonConditionType ToCommonConditionType(MoveStopConditionType moveStopConditionType, BattleSide battleSide)
        {
            return moveStopConditionType switch
            {
                MoveStopConditionType.None => InGameCommonConditionType.None,

                MoveStopConditionType.ElapsedTime => InGameCommonConditionType.ElapsedTimeSinceMoveStarted,

                MoveStopConditionType.TargetPosition => battleSide == BattleSide.Enemy
                    ? InGameCommonConditionType.EnemyUnitTargetPosition
                    : InGameCommonConditionType.None,

                MoveStopConditionType.PassedKomaCount => InGameCommonConditionType.PassedKomaCountSinceMoveStart,

                _ => InGameCommonConditionType.None
            };
        }
    }
}
