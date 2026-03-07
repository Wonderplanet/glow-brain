using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkFragmentModel(
        MasterDataId Id,
        MasterDataId MstArtworkId,
        MasterDataId MstDropGroupId,
        Percentage DropPercentage,
        Rarity Rarity,
        ArtworkFragmentAssetNum AssetNum,
        ArtworkFragmentName Name,
        ArtworkFragmentPositionNum Position
    )
    {
        public static MstArtworkFragmentModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            Percentage.Empty,
            Rarity.R,
            ArtworkFragmentAssetNum.Empty,
            ArtworkFragmentName.Empty,
            ArtworkFragmentPositionNum.Empty
        );

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
