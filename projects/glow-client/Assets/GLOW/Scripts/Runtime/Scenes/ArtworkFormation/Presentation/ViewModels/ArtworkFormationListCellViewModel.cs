using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkFormation.Presentation.ViewModels
{
    public record ArtworkFormationListCellViewModel(
        MasterDataId MstArtworkId,
        ArtworkAssetPath AssetPath,
        AssignedFlag IsAssigned,
        Rarity Rarity,
        ArtworkGradeLevel Grade,
        ArtworkCompleteFlag IsCompleted,
        ArtworkFragmentPanelViewModel ArtworkFragmentPanelViewModel,
        ArtworkGrayOutFlag IsGrayOut)
    {
        public static ArtworkFormationListCellViewModel Empty { get; } =
            new ArtworkFormationListCellViewModel(
                MasterDataId.Empty,
                ArtworkAssetPath.Empty,
                AssignedFlag.Unassigned,
                Rarity.R,
                ArtworkGradeLevel.Empty,
                ArtworkCompleteFlag.False,
                ArtworkFragmentPanelViewModel.Empty,
                ArtworkGrayOutFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
    }
}