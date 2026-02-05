using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerDeckSpecialAttackAction(DeckUnitIndex DeckUnitIndex) : IAutoPlayerAction
    {
        public static AutoPlayerDeckSpecialAttackAction Empty { get; } = new (DeckUnitIndex.Empty);

        AutoPlayerSequenceActionType IAutoPlayerAction.Type => AutoPlayerSequenceActionType.PlayerSpecialAttack;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AutoPlayerDeckSpecialAttackAction IAutoPlayerAction.ToDeckSpecialAttackAction()
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

        AutoPlayerSummonGimmickObjectAction IAutoPlayerAction.ToSummonGimmickObjectAction()
        {
            return AutoPlayerSummonGimmickObjectAction.Empty;
        }

        AutoPlayerTransformGimmickObjectToEnemyAction IAutoPlayerAction.ToTransformGimmickObjectToEnemyAction()
        {
            return AutoPlayerTransformGimmickObjectToEnemyAction.Empty;
        }

        AutoPlayerOpponentRushExecuteAction IAutoPlayerAction.ToOpponentRushAction()
        {
            return AutoPlayerOpponentRushExecuteAction.Empty;
        }
    }
}
