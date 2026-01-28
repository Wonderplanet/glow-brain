using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerSummonDeckSpecialUnitAction(
        DeckUnitIndex DeckUnitIndex,
        PageCoordV2 SummonPosition) : IAutoPlayerAction
    {
        public static AutoPlayerSummonDeckSpecialUnitAction Empty { get; } = new (
            DeckUnitIndex.Empty,
            PageCoordV2.Empty);

        AutoPlayerSequenceActionType IAutoPlayerAction.Type => AutoPlayerSequenceActionType.SummonPlayerSpecialCharacter;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AutoPlayerSummonDeckSpecialUnitAction IAutoPlayerAction.ToSummonDeckSpecialUnitAction()
        {
            return this;
        }

        AutoPlayerDeckSpecialAttackAction IAutoPlayerAction.ToDeckSpecialAttackAction()
        {
            return AutoPlayerDeckSpecialAttackAction.Empty;
        }

        AutoPlayerSummonEnemyAction IAutoPlayerAction.ToSummonEnemyAction()
        {
            return AutoPlayerSummonEnemyAction.Empty;
        }

        AutoPlayerSummonDeckUnitAction IAutoPlayerAction.ToSummonDeckUnitAction()
        {
            return AutoPlayerSummonDeckUnitAction.Empty;
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
