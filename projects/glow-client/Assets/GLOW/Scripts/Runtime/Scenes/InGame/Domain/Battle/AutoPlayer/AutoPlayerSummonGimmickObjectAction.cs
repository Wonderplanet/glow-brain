using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerSummonGimmickObjectAction(InGameGimmickObjectGenerationModel InGameGimmickObjectGenerationModel) : IAutoPlayerAction
    {
        public static AutoPlayerSummonGimmickObjectAction Empty { get; } = new (InGameGimmickObjectGenerationModel.Empty);

        AutoPlayerSequenceActionType IAutoPlayerAction.Type => AutoPlayerSequenceActionType.SummonGimmickObject;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AutoPlayerSummonGimmickObjectAction IAutoPlayerAction.ToSummonGimmickObjectAction()
        {
            return this;
        }

        AutoPlayerSummonEnemyAction IAutoPlayerAction.ToSummonEnemyAction()
        {
            return AutoPlayerSummonEnemyAction.Empty;
        }

        AutoPlayerSummonDeckUnitAction IAutoPlayerAction.ToSummonDeckUnitAction()
        {
            return AutoPlayerSummonDeckUnitAction.Empty;
        }

        AutoPlayerSummonDeckSpecialUnitAction IAutoPlayerAction.ToSummonDeckSpecialUnitAction()
        {
            return AutoPlayerSummonDeckSpecialUnitAction.Empty;
        }

        AutoPlayerTransformGimmickObjectToEnemyAction IAutoPlayerAction.ToTransformGimmickObjectToEnemyAction()
        {
            return AutoPlayerTransformGimmickObjectToEnemyAction.Empty;
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
