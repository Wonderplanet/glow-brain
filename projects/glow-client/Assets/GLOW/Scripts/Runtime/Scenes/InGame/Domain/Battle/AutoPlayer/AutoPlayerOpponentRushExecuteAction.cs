using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerOpponentRushExecuteAction(ExecuteRushFlag ExecuteRushFlag) : IAutoPlayerAction
    {
        public static AutoPlayerOpponentRushExecuteAction Empty { get; } = new(ExecuteRushFlag.False);

        public static AutoPlayerOpponentRushExecuteAction True { get; } = new(ExecuteRushFlag.True);
        public static AutoPlayerOpponentRushExecuteAction False { get; } = new(ExecuteRushFlag.False);

        AutoPlayerSequenceActionType IAutoPlayerAction.Type => AutoPlayerSequenceActionType.OpponentRush;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        AutoPlayerOpponentRushExecuteAction IAutoPlayerAction.ToOpponentRushAction()
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
    }
}
