using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitRoleBonusModel(
        MasterDataId Id,
        CharacterUnitRoleType RoleType,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        CharacterColorAdvantageDefenseBonus ColorAdvantageDefenseBonus);
}
