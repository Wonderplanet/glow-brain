using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceSpecialAttackViewModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime InitialCoolTime,
        SpecialAttackCoolTime CoolTime,
        CharacterUnitRoleType RoleType);
}
