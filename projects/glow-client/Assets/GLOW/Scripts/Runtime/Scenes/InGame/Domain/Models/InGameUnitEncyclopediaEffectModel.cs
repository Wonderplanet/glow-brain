using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameUnitEncyclopediaEffectModel(
        PercentageM HpEffectRate,
        PercentageM AttackPowerEffectRate,
        PercentageM HealEffectRate
    )
    {
        public static InGameUnitEncyclopediaEffectModel Empty { get; } = new(
            PercentageM.Empty,
            PercentageM.Empty,
            PercentageM.Empty
        );
    }
}
