using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFormation.Presentation.ViewModels
{
    public record ArtworkFormationPartyCellViewModel(
        MasterDataId MstArtworkId,
        ArtworkAssetPath AssetPath,
        Rarity Rarity,
        ArtworkGradeLevel Grade)
    {
        public static ArtworkFormationPartyCellViewModel Empty { get; } = 
            new ArtworkFormationPartyCellViewModel(
                MasterDataId.Empty,
                ArtworkAssetPath.Empty,
                Rarity.R,
                ArtworkGradeLevel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}