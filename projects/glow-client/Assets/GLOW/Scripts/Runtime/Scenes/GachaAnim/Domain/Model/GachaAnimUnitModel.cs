using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.GachaAnim.Domain.Model
{
    public record GachaAnimUnitModel(
        MasterDataId Id,
        CharacterName Name,
        Rarity Rarity,
        CharacterUnitRoleType CharacterUnitRoleType,
        CharacterColor CharacterColor,
        SeriesAssetKey SeriesAssetKey,
        UnitAssetKey UnitAssetKey,
        SpeechBalloonText SpeechBalloonText
    )
    {
        public static GachaAnimUnitModel Empty { get; } = new(
            MasterDataId.Empty,
            CharacterName.Empty,
            Rarity.R,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            SeriesAssetKey.Empty,
            UnitAssetKey.Empty,
            SpeechBalloonText.Empty
            );
    }
}
