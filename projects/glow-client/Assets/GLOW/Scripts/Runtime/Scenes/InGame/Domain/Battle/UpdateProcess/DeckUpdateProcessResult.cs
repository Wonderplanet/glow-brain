using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record DeckUpdateProcessResult(
        IReadOnlyList<DeckUnitModel> UpdatedDeckUnits,
        IReadOnlyList<DeckUnitModel> UpdatedPvpOpponentDeckUnits)
    {
        public static DeckUpdateProcessResult Empty { get; } = new (
            new List<DeckUnitModel>(),
            new List<DeckUnitModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
