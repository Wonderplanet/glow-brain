using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.ViewModels
{
    public record EncyclopediaUnitDetailViewModel(
        CharacterUnitRoleType RoleType,
        Rarity Rarity,
        CharacterName Name,
        UnitAssetKey UnitAssetKey,
        SeriesLogoImagePath SeriesLogoImagePath,
        UnitDescription Description,
        SpecialAttackName SpecialAttackName);
}
