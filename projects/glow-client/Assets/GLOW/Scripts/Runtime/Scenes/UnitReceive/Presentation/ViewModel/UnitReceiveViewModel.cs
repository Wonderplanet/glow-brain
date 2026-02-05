using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitReceive.Presentation.ViewModel
{
    public record UnitReceiveViewModel(
        CharacterName CharacterName,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        UnitCutInKomaAssetPath UnitCutInKomaAssetPath,
        UnitImageAssetPath UnitImageAssetPath,
        SeriesLogoImagePath SeriesLogoImagePath,
        SpeechBalloonText SpeechBalloonText)
    {
        public static UnitReceiveViewModel Empty { get; } = new(
            CharacterName.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            Rarity.R,
            UnitCutInKomaAssetPath.Empty,
            UnitImageAssetPath.Empty,
            SeriesLogoImagePath.Empty,
            SpeechBalloonText.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
