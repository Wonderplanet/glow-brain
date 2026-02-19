using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceSpecialAttackModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime InitialCoolTime,
        SpecialAttackCoolTime CoolTime,
        CharacterUnitRoleType RoleType);
}
