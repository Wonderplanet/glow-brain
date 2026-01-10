using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkModel(
        MasterDataId Id,
        MasterDataId MstSeriesId,
        HP OutpostAdditionalHp,
        ArtworkAssetKey AssetKey,
        SortOrder SortOrder,
        ArtworkName Name,
        ArtworkDescription Description)
    {
        public static MstArtworkModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            HP.Empty,
            ArtworkAssetKey.Empty,
            SortOrder.Empty,
            ArtworkName.Empty,
            ArtworkDescription.Empty
        );
    };
}
