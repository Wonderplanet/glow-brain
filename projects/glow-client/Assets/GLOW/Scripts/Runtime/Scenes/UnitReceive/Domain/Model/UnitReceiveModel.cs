using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.UnitReceive.Domain.Model
{
    public record UnitReceiveModel(
        CharacterName CharacterName,
        CharacterUnitRoleType RoleType,
        CharacterColor CharacterColor,
        Rarity Rarity,
        UnitCutInKomaAssetPath UnitCutInKomaAssetPath,
        UnitImageGetStandAssetPath UnitImageGetStandAssetPath,
        SeriesLogoImagePath SeriesLogoImagePath,
        SpeechBalloonText SpeechBalloonText)
    {
        public static UnitReceiveModel Empty { get; } = new(
            CharacterName.Empty,
            CharacterUnitRoleType.Balance,
            CharacterColor.Blue,
            Rarity.R,
            UnitCutInKomaAssetPath.Empty,
            UnitImageGetStandAssetPath.Empty,
            SeriesLogoImagePath.Empty,
            SpeechBalloonText.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
