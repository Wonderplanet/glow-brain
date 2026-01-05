using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IDeckUpdateProcess
    {
        DeckUpdateProcessResult Update(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<SpecialUnitModel> removedSpecialUnits,
            TickCount tickCount);
    }
}
