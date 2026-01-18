using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstEnemyCharacterModel(
        MasterDataId Id,
        MasterDataId MstSeriesId,
        UnitAssetKey AssetKey,
        CharacterName Name,
        UnitDescription Description,
        VisibleOnEncyclopediaFlag VisibleOnEncyclopediaFlag,
        PhantomizedFlag IsPhantomized)
    {
        public static MstEnemyCharacterModel Empty { get; } = new MstEnemyCharacterModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            UnitAssetKey.Empty,
            CharacterName.Empty,
            UnitDescription.Empty,
            VisibleOnEncyclopediaFlag.Empty,
            PhantomizedFlag.False
        );
    }
}
