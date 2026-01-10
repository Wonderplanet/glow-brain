using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaDisplayUnitViewModel(
        MasterDataId UnitId,
        CharacterName Name,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        SeriesLogoImagePath SeriesLogoImagePath,
        GachaContentCutInAssetPath ContentCutInAssetPath,
        GachaDisplayUnitDescription DisplayUnitDescription
        );
}
