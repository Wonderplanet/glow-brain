using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record AttackProcessApplyingPlaceItemResult(IReadOnlyList<PlacedItemModel> AppliedPlaceItems)
    {
        public static AttackProcessApplyingPlaceItemResult Empty { get; } = new (new List<PlacedItemModel>());
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}