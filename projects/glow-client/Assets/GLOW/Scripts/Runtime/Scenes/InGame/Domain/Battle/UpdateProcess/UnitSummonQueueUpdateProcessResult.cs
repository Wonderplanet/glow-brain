using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record UnitSummonQueueUpdateProcessResult(
        IReadOnlyList<CharacterUnitModel> SummonUnitModelList,
        IReadOnlyList<CharacterUnitModel> UpdatedUnitModelList,
        UnitSummonQueueModel UpdatedUnitSummonQueueModel,
        DeckUnitSummonQueueModel UpdatedDeckUnitSummonQueueModel)
    {
        public static UnitSummonQueueUpdateProcessResult Empty { get; } = new(
            new List<CharacterUnitModel>(),
            new List<CharacterUnitModel>(),
            UnitSummonQueueModel.Empty,
            DeckUnitSummonQueueModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
