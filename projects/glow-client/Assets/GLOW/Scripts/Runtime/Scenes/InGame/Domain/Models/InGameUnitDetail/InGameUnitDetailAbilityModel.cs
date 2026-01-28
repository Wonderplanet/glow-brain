using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail
{
    public record InGameUnitDetailAbilityModel(
        UnitAbility Ability,
        bool IsLock);
}
