using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkList.Presentation.ViewModels
{
    public record ArtworkListCellViewModel(
        MasterDataId MstArtworkId,
        ArtworkCompleteFlag IsCompleted,
        ArtworkFragmentPanelViewModel ArtworkFragmentPanelViewModel,
        Rarity Rarity,
        ArtworkGradeLevel Grade)
    {
        // ArtworkFormationListCellViewModelとの互換性のため、IsAssignedを追加（常にfalse）
        public AssignedFlag IsAssigned => AssignedFlag.Unassigned;
        
        public static ArtworkListCellViewModel Empty { get; } = new ArtworkListCellViewModel(
            MasterDataId.Empty,
            ArtworkCompleteFlag.False,
            ArtworkFragmentPanelViewModel.Empty,
            Rarity.R,
            ArtworkGradeLevel.Empty);

        public bool IsEmpty()
        {
            return MstArtworkId.IsEmpty();
        }
    }
}

