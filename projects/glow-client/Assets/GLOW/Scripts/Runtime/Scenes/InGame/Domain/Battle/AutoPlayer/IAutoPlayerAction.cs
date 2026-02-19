using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public interface IAutoPlayerAction
    {
        AutoPlayerSequenceActionType Type { get; }

        AutoPlayerSummonEnemyAction ToSummonEnemyAction();
        AutoPlayerSummonDeckUnitAction ToSummonDeckUnitAction();
        AutoPlayerSummonDeckSpecialUnitAction ToSummonDeckSpecialUnitAction();
        AutoPlayerSummonGimmickObjectAction ToSummonGimmickObjectAction();
        AutoPlayerTransformGimmickObjectToEnemyAction ToTransformGimmickObjectToEnemyAction();
        AutoPlayerDeckSpecialAttackAction ToDeckSpecialAttackAction();
        AutoPlayerOpponentRushExecuteAction ToOpponentRushAction();
    }
}
