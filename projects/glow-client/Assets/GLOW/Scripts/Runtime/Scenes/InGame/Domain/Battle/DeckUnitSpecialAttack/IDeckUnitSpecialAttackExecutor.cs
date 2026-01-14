using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckUnitSpecialAttackExecutor
    {
        DeckUnitSpecialAttackResult UseSpecialAttack(
            DeckUnitModel deckUnit,
            IReadOnlyList<CharacterUnitModel> units,
            BattleSide battleSide);
    }
}
