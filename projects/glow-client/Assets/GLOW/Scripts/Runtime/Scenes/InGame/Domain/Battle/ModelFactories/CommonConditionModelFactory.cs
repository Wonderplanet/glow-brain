using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class CommonConditionModelFactory : ICommonConditionModelFactory
    {
        [Inject] ICoordinateConverter CoordinateConverter { get; }

        public ICommonConditionModel Create(
            InGameCommonConditionType type, 
            CommonConditionValue value)
        {
            return type switch
            {
                InGameCommonConditionType.Always =>
                    AlwaysCommonConditionModel.Instance,

                InGameCommonConditionType.MyHpMoreThanOrEqualPercentage => 
                    new MyHpMoreThanOrEqualPercentageCommonConditionModel(value.ToPercentage()),
                
                InGameCommonConditionType.MyHpLessThanOrEqualPercentage => 
                    new MyHpLessThanOrEqualPercentageCommonConditionModel(value.ToPercentage()),
                
                InGameCommonConditionType.MyDamage =>
                    new MyDamageCommonConditionModel(value.ToHP()),

                InGameCommonConditionType.EnemyOutpostDamage =>
                    new EnemyOutpostDamageCommonConditionModel(value.ToHP()),

                InGameCommonConditionType.EnemyUnitDead =>
                    new EnemyUnitDeadCommonConditionModel(value.ToAutoPlayerSequenceElementId()),

                InGameCommonConditionType.DeadEnemyUnitCount =>
                    new DeadEnemyUnitCountCommonConditionModel(value.ToDefeatEnemyCount()),

                InGameCommonConditionType.StageTime =>
                    new StageTimeCommonConditionModel(value.ToTickCount()),

                InGameCommonConditionType.ElapsedTimeSinceSummoned =>
                    new ElapsedTimeSinceSummonedCommonConditionModel(value.ToTickCount()),

                InGameCommonConditionType.StageReady =>
                    StageReadyCommonConditionModel.Instance,

                InGameCommonConditionType.PlayerUnitEnterSpecificKoma =>
                    new PlayerUnitEnterSpecificKomaCommonConditionModel(value.ToKomaNo()),

                InGameCommonConditionType.PlayerUnitEnterSameKoma =>
                    PlayerUnitEnterSameKomaCommonConditionModel.Instance,

                InGameCommonConditionType.DarknessKomaCleared =>
                    new DarknessKomaClearedCommonConditionModel(value.ToKomaNo()),

                InGameCommonConditionType.EnemyUnitTransformed =>
                    new EnemyUnitTransformedCommonConditionModel(value.ToAutoPlayerSequenceElementId()),

                InGameCommonConditionType.EnemyUnitTransformDead =>
                    new EnemyUnitTransformedDeadCommonConditionModel(value.ToAutoPlayerSequenceElementId()),

                InGameCommonConditionType.EnemySequenceElementActivated =>
                    new EnemySequenceElementActivatedCommonConditionModel(value.ToAutoPlayerSequenceElementId()),

                InGameCommonConditionType.ElapsedTimeSinceEnemySequenceGroupActivated =>
                    new ElapsedTimeSinceEnemySequenceGroupActivatedCommonConditionModel(value.ToTickCount()),

                InGameCommonConditionType.EnemyUnitSummoned =>
                    new EnemyUnitSummonedCommonConditionModel(value.ToAutoPlayerSequenceElementId()),

                InGameCommonConditionType.EnemyUnitTargetPosition =>
                    new EnemyUnitTargetPositionCommonConditionModel(
                        CoordinateConverter.FieldToEnemyOutpostCoord(value.ToFieldCoord())),

                InGameCommonConditionType.PassedKomaCountSinceMoveStart =>
                    new PassedKomaCountSinceMoveStartCommonConditionModel(value.ToPassedKomaCount()),

                InGameCommonConditionType.ElapsedTimeSinceMoveStopped =>
                    new ElapsedTimeSinceMoveStoppedCommonConditionModel(value.ToTickCount()),

                InGameCommonConditionType.ElapsedTimeSinceMoveStarted =>
                    new ElapsedTimeSinceMoveStartedCommonConditionModel(value.ToTickCount()),

                InGameCommonConditionType.EnemyOutpostHpPercentage =>
                    new EnemyOutpostHpPercentageCommonConditionModel(value.ToPercentageM()),

                _ => EmptyCommonConditionModel.Instance
            };
        }
    }
}
