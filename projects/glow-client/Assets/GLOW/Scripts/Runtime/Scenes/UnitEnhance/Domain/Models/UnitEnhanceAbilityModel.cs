using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceAbilityModel(UnitAbility Ability, UnitLevel UnlockUnitLevel, bool IsLock)
    {
        public static UnitEnhanceAbilityModel Empty => new (
            UnitAbility.Empty,
            UnitLevel.Empty,
            false);
    }
}
