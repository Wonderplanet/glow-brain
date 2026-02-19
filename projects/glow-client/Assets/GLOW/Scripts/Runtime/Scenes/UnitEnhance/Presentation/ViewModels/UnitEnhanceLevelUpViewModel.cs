using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceLevelUpViewModel(
        Coin LevelUpCost,
        EnoughCostFlag IsEnoughCost
    )
    {
        public static UnitEnhanceLevelUpViewModel Empty { get; } = new(
            Coin.Zero,
            EnoughCostFlag.False);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
