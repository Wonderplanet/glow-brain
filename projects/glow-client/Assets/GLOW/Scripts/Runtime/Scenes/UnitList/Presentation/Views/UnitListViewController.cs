using System.Collections.Generic;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.UnitList.Presentation.ViewModels;
using GLOW.Scenes.UnitTab.Presentation.Views.Components;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitList.Presentation.Views
{
    public class UnitListViewController : HomeBaseViewController<UnitListView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IUnitListFilterAndSortDelegate
    {
        [Inject] IUnitListViewDelegate ViewDelegate { get; }
        IReadOnlyList<UnitListCellViewModel> _unitViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.UnitList.Delegate = this;
            ActualView.UnitList.DataSource = this;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ActualView.FilterAndSort.Delegate = this;
            ViewDelegate.ViewWillAppear();
        }

        public void Setup(UnitListViewModel viewModel)
        {
            _unitViewModels = viewModel.Units;
            ActualView.FilterAndSort.Setup(viewModel.CategoryModel.SortOrder, viewModel.CategoryModel.IsAnyFilter());
            ActualView.UnitList.ReloadData();
        }

        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        public float GetScrollVerticalNormalizedPosition()
        {
            return ActualView.ScrollVerticalNormalizedPosition;
        }

        public void SetScrollVerticalNormalizedPosition(float value)
        {
            ActualView.ScrollVerticalNormalizedPosition = value;
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _unitViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _unitViewModels[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<UnitListCellComponent>(item => item.UserUnitId == viewModel.UserUnitId);
            cell.Setup(viewModel);

            cell.Button.onClick.RemoveAllListeners();
            cell.Button.onClick.AddListener(() =>
            {
                ViewDelegate.OnSelectUnit(viewModel.UserUnitId);
            });

            return cell;
        }
        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath) { }
        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier) { }


        void IUnitListFilterAndSortDelegate.OnSortAndFilter()
        {
            ViewDelegate.OnSortAndFilter();
        }

        void IUnitListFilterAndSortDelegate.OnSortAscending()
        {
            ViewDelegate.OnSortAscending();
        }
        void IUnitListFilterAndSortDelegate.OnSortDescending()
        {
            ViewDelegate.OnSortDescending();
        }
    }
}
