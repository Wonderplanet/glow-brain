using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail
{
    public record InGameUnitDetailAbilityViewModel(
        UnitAbility Ability,
        bool IsLock);
}
