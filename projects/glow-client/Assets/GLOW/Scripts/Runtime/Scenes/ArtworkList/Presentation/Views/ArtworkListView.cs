using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFormation.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkList.Presentation.Views
{
    public class ArtworkListView : UIView
    {
        [SerializeField] ArtworkFormationListComponent _listComponent;
        [SerializeField] ArtworkSortAndFilterButtonComponent _sortAndFilterButtonComponent;

        public void SetUp(
            ArtworkFormationListViewModel viewModel,
            IArtworkFormationListComponentDelegate listDelegate)
        {
            _listComponent.SetUp(viewModel);
            _listComponent.Delegate = listDelegate;
            _sortAndFilterButtonComponent.SetSortAllow(
                viewModel.SortFilterCategoryModel.SortOrder,
                viewModel.SortFilterCategoryModel.IsAnyFilter());
        }
    }
}

