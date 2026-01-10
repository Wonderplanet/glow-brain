using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IUnitSummonQueueUpdateProcess
    {
        UnitSummonQueueUpdateProcessResult UpdateUnitSummonQueue(
            UnitSummonQueueModel unitSummonQueue,
            BossSummonQueueModel bossSummonQueueModel,
            DeckUnitSummonQueueModel deckUnitSummonQueueModel,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            TickCount tickCount);
    }
}
