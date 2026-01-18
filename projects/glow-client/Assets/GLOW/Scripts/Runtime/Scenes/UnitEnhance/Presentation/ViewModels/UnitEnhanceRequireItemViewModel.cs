using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceRequireItemViewModel(
        ItemIconViewModel ItemIcon,
        EnoughCostFlag IsEnoughCost
    )
    {
        public static UnitEnhanceRequireItemViewModel Empty { get; } = new(
            ItemIconViewModel.Empty,
            EnoughCostFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
