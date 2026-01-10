using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Domain.Model
{
    public record GachaDisplayUnitModel(
        MasterDataId UnitId,
        CharacterName Name,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        SeriesLogoImagePath SeriesLogoImagePath,
        GachaContentCutInAssetPath CutInAssetPath,
        GachaDisplayUnitDescription DisplayUnitDescription);
}
