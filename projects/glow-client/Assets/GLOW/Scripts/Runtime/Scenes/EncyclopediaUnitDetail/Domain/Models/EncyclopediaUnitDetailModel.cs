using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Domain.Models
{
    public record EncyclopediaUnitDetailModel(
        CharacterUnitRoleType RoleType,
        Rarity Rarity,
        CharacterName Name,
        UnitAssetKey UnitAssetKey,
        SeriesLogoImagePath SeriesLogoImagePath,
        UnitDescription Description,
        SpecialAttackName SpecialAttackName);
}
