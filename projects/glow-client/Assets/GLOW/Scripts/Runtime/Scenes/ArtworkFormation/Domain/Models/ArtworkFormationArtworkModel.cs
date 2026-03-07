using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.ArtworkFragment.Domain.Model;

namespace GLOW.Scenes.ArtworkFormation.Domain.Models
{
    public record ArtworkFormationArtworkModel(
        MasterDataId MstArtworkId,
        ArtworkAssetPath AssetPath,
        Rarity Rarity,
        ArtworkCompleteFlag IsCompleted,
        ArtworkPanelModel ArtworkPanelModel,
        ArtworkGradeLevel Grade)
    {
        public static ArtworkFormationArtworkModel Empty { get; } =
            new ArtworkFormationArtworkModel(
                MasterDataId.Empty,
                ArtworkAssetPath.Empty,
                Rarity.UR,
                ArtworkCompleteFlag.False,
                ArtworkPanelModel.Empty,
                ArtworkGradeLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}