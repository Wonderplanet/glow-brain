using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFormation.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views
{
    public class ArtworkFormationView : UIView
    {
        [SerializeField] ArtworkFormationListComponent _listComponent;
        [SerializeField] ArtworkFormationPartyComponent _partyComponent;
        [SerializeField] ArtworkSortAndFilterButtonComponent _sortAndFilterButtonComponent;

        public void InitializeView(
            IArtworkFormationListComponentDelegate listComponentDelegate,
            IArtworkFormationPartyComponentDelegate partyComponentDelegate)
        {
            _listComponent.Delegate = listComponentDelegate;
            _partyComponent.Delegate = partyComponentDelegate;
        }

        public void SetUp(ArtworkFormationViewModel viewModel)
        {
            _partyComponent.SetUp(viewModel.PartyViewModel);
            _listComponent.SetUp(viewModel.ListViewModel);
            _sortAndFilterButtonComponent.SetSortAllow(
                viewModel.ListViewModel.SortFilterCategoryModel.SortOrder,
                viewModel.ListViewModel.SortFilterCategoryModel.IsAnyFilter());
        }

        public void UpdatePartyComponent(ArtworkFormationPartyViewModel partyViewModel)
        {
            _partyComponent.SetUp(partyViewModel);
        }

        public void UpdateListComponent(ArtworkFormationListViewModel listViewModel)
        {
            _listComponent.SetUp(listViewModel);
            _sortAndFilterButtonComponent.SetSortAllow(
                listViewModel.SortFilterCategoryModel.SortOrder,
                listViewModel.SortFilterCategoryModel.IsAnyFilter());
        }

        public void UpdateListCellAssignment(
            MasterDataId mstArtworkId,
            ArtworkFormationListCellViewModel cellViewModel,
            ArtworkFormationListCell targetCell = null)
        {
            _listComponent.UpdateCellAssignment(mstArtworkId, cellViewModel);
        }
    }
}

