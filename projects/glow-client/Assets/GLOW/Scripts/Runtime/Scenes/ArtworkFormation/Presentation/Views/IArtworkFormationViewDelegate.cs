using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views
{
    public interface IArtworkFormationViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewWillDisappear();
        void OnIncompleteArtworkTapped(MasterDataId mstArtworkId);
        void OnArtworkTappedWhenPartyFull(MasterDataId mstArtworkId);
        void OnTapRemoveLastArtwork(MasterDataId mstArtworkId);
        void OnArtworkAssignmentToggled(MasterDataId mstArtworkId, bool isCurrentlyAssigned);
        void OnListCellLongTapped(MasterDataId mstArtworkId, List<ArtworkFormationListCellViewModel> cellViewModels);
        void OnRecommendButtonTapped();
        void OnSortAndFilterButtonTapped();
        void OnSortButtonTapped();
        void OnHelpButtonTapped();
        ArtworkFormationPartyViewModel GetPartyViewModel();
        ArtworkFormationListViewModel GetListViewModel();
        ArtworkFormationListCellViewModel GetListCellViewModel(MasterDataId mstArtworkId);
        IReadOnlyList<MasterDataId> GetAssignedArtworkIds();
    }
}