using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface ICharacterUnitActionFactory
    {
        ICharacterUnitAction CreateAttackHitAction(
            AttackHitData attackHitData,
            AttackHitData attachHitDataForNextHitAction, // attackHitDataによるActionの次にするAction
            CharacterUnitModel unit,
            StageTimeModel stageTime);
    }
}
