using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.GachaAnim.Presentation.ViewModels
{
    public record GachaAnimResultViewModel(
        ResourceType ResourceType,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        SeriesLogoImagePath SeriesLogoImagePath,
        GachaAnimationUnitInfoAssetPath GachaAnimationUnitInfoAssetPath,
        CharacterName CharacterName,
        PlayerResourceIconAssetPath ItemIconAssetPath,
        PlayerResourceName ItemName,
        GachaAnimNewFlg NewFlg,
        UnitImageAssetPath UnitImageAssetPath,
        SpeechBalloonText SpeechBalloonText
    );
}
