using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceLevelUpModel(
        Coin LevelUpCost,
        EnoughCostFlag IsEnoughCost
    )
    {
        public static UnitEnhanceLevelUpModel Empty { get; } = new(
            Coin.Zero,
            EnoughCostFlag.False);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
