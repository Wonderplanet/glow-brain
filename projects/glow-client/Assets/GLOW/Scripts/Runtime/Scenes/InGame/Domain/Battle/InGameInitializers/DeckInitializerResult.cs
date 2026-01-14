using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record DeckInitializerResult(
        IReadOnlyList<DeckUnitModel> DeckUnits,
        IReadOnlyList<DeckUnitModel> PvpOpponentDeckUnits)
    {
        public static DeckInitializerResult Empty { get; } = new (
            new List<DeckUnitModel>(),
            new List<DeckUnitModel>());
    }
}
