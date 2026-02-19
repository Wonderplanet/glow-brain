using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceAbilityViewModel(
        UnitAbility Ability,
        UnitLevel UnlockUnitLevel,
        bool IsLock)
    {
        public static UnitEnhanceAbilityViewModel Empty { get; } = new(
            UnitAbility.Empty,
            UnitLevel.Empty,
            false);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
