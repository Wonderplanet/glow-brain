using GLOW.Scenes.EncyclopediaTop.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-1_図鑑TOP画面
    /// </summary>
    public class EncyclopediaTopViewController : HomeBaseViewController<EncyclopediaTopView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [Inject] IEncyclopediaTopViewDelegate ViewDelegate { get; }

        EncyclopediaTopViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeView();
            ActualView.CollectionView.Delegate = this;
            ActualView.CollectionView.DataSource = this;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(EncyclopediaTopViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel.TotalGrade, viewModel.BonusBadge);
            ActualView.CollectionView.ReloadData();
        }
        
        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.Cells.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EncyclopediaTopSeriesCell>();
            cell.Setup(_viewModel.Cells[indexPath.Row]);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cellViewModel = _viewModel.Cells[indexPath.Row];
            ViewDelegate.OnSelectSeries(cellViewModel.MstSeriesId);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }

        [UIAction]
        void OnSelectEncyclopediaBonusButton()
        {
            ViewDelegate.OnSelectEncyclopediaBonusButton();
        }

        [UIAction]
        void OnSelectSortButton()
        {
            ViewDelegate.OnSelectSortButton();
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }
    }
}
