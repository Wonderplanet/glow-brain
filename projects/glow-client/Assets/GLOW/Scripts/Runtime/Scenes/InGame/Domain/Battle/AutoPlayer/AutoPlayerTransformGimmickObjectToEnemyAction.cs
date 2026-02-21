using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerTransformGimmickObjectToEnemyAction(
        MasterDataId EnemyId,
        UnitGenerationModel UnitGenerationModel,
        AutoPlayerSequenceElementId TransformTargetGimmickSequenceElementId) : IAutoPlayerAction
    {
        public static AutoPlayerTransformGimmickObjectToEnemyAction Empty { get; } = new (
            MasterDataId.Empty,
            UnitGenerationModel.Empty,
            AutoPlayerSequenceElementId.Empty);

        AutoPlayerSequenceActionType IAutoPlayerAction.Type => AutoPlayerSequenceActionType.TransformGimmickObjectToEnemy;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AutoPlayerTransformGimmickObjectToEnemyAction IAutoPlayerAction.ToTransformGimmickObjectToEnemyAction()
        {
            return this;
        }

        AutoPlayerSummonDeckUnitAction IAutoPlayerAction.ToSummonDeckUnitAction()
        {
            return AutoPlayerSummonDeckUnitAction.Empty;
        }

        AutoPlayerSummonDeckSpecialUnitAction IAutoPlayerAction.ToSummonDeckSpecialUnitAction()
        {
            return AutoPlayerSummonDeckSpecialUnitAction.Empty;
        }

        AutoPlayerSummonEnemyAction IAutoPlayerAction.ToSummonEnemyAction()
        {
            return AutoPlayerSummonEnemyAction.Empty;
        }

        AutoPlayerSummonGimmickObjectAction IAutoPlayerAction.ToSummonGimmickObjectAction()
        {
            return AutoPlayerSummonGimmickObjectAction.Empty;
        }

        AutoPlayerDeckSpecialAttackAction IAutoPlayerAction.ToDeckSpecialAttackAction()
        {
            return AutoPlayerDeckSpecialAttackAction.Empty;
        }

        AutoPlayerOpponentRushExecuteAction IAutoPlayerAction.ToOpponentRushAction()
        {
            return AutoPlayerOpponentRushExecuteAction.Empty;
        }
    }
}
