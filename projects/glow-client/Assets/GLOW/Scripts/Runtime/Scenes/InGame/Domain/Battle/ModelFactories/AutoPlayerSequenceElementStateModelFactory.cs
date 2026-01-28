using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class AutoPlayerSequenceElementStateModelFactory : IAutoPlayerSequenceElementStateModelFactory
    {
        [Inject] ICommonConditionModelFactory CommonConditionModelFactory { get; }

        public AutoPlayerSequenceElementStateModel Create(MstAutoPlayerSequenceElementModel elementModel, BattleSide battleSide)
        {
            var activationConditionType = ToCommonConditionType(elementModel.ActivationCondition.Type, battleSide);
            var activationCondition = CommonConditionModelFactory.Create(
                activationConditionType,
                elementModel.ActivationCondition.Value.ToCommonConditionValue());

            var deactivationConditionType = ToCommonConditionType(elementModel.DeactivationCondition.Type, battleSide);
            var deactivationCondition = CommonConditionModelFactory.Create(
                deactivationConditionType,
                elementModel.DeactivationCondition.Value.ToCommonConditionValue());

            return new AutoPlayerSequenceElementStateModel(
                elementModel,
                activationCondition,
                deactivationCondition,
                AutoPlayerSequenceElementActivatedFlag.False,
                AutoPlayerSequenceElementDeactivatedFlag.False,
                new TickCount(0),
                elementModel.ActionDelay,
                elementModel.SummonCount);
        }

        InGameCommonConditionType ToCommonConditionType(AutoPlayerSequenceConditionType sequenceConditionType, BattleSide battleSide)
        {
            return sequenceConditionType switch
            {
                AutoPlayerSequenceConditionType.InitialSummon => InGameCommonConditionType.StageReady,
                AutoPlayerSequenceConditionType.ElapsedTime => InGameCommonConditionType.StageTime,
                AutoPlayerSequenceConditionType.DarknessKomaCleared => InGameCommonConditionType.DarknessKomaCleared,

                AutoPlayerSequenceConditionType.FriendUnitDead =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemyUnitDead
                        : InGameCommonConditionType.None,

                AutoPlayerSequenceConditionType.OutpostDamage =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemyOutpostDamage
                        : InGameCommonConditionType.None,

                AutoPlayerSequenceConditionType.OutpostHpPercentage =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemyOutpostHpPercentage
                        : InGameCommonConditionType.None,

                AutoPlayerSequenceConditionType.EnterTargetKomaIndex =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.PlayerUnitEnterSpecificKoma
                        : InGameCommonConditionType.None,

                AutoPlayerSequenceConditionType.FriendUnitTransform =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemyUnitTransformed
                        : InGameCommonConditionType.None,
                AutoPlayerSequenceConditionType.FriendUnitSummoned =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemyUnitSummoned
                        : InGameCommonConditionType.None,
                AutoPlayerSequenceConditionType.SequenceElementActivated =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.EnemySequenceElementActivated
                        : InGameCommonConditionType.None,
                AutoPlayerSequenceConditionType.ElapsedTimeSinceSequenceGroupActivated =>
                    battleSide == BattleSide.Enemy
                        ? InGameCommonConditionType.ElapsedTimeSinceEnemySequenceGroupActivated
                        : InGameCommonConditionType.None,
                _ => InGameCommonConditionType.None
            };
        }
    }
}
