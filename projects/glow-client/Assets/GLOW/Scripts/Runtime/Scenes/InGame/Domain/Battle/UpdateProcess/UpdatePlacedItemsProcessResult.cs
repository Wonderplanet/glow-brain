using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record UpdatePlacedItemsProcessResult(
        IReadOnlyList<PlacedItemModel> NewPlacedItems,
        IReadOnlyList<PlacedItemModel> UpdatedItems,
        IReadOnlyList<PlacedItemModel> ConsumedItems)
    {
        public static UpdatePlacedItemsProcessResult Empty { get; } = new UpdatePlacedItemsProcessResult(
            new List<PlacedItemModel>(),
            new List<PlacedItemModel>(),
            new List<PlacedItemModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}