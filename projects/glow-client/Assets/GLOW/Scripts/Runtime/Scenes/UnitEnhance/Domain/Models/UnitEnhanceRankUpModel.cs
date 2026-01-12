using System.Collections.Generic;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceRankUpModel(
        IReadOnlyList<UnitEnhanceRequireItemModel> CostItems
    )
    {
        public static UnitEnhanceRankUpModel Empty { get; } = new(new List<UnitEnhanceRequireItemModel>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
