using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceRankUpViewModel(IReadOnlyList<UnitEnhanceRequireItemViewModel> CostItems)
    {
        public static UnitEnhanceRankUpViewModel Empty { get; } = new(new List<UnitEnhanceRequireItemViewModel>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
