using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopTopViewController :
        UIViewController<ExchangeShopTopView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        public record Argument(MasterDataId MstExchangeId);
        [Inject] IExchangeShopTopViewDelegate ViewDelegate { get; }

        ExchangeShopTopViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public void InitializeView()
        {
            ActualView.InitializeView(this, this);
        }

        public void SetUpView(ExchangeShopTopViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel);
        }

        public void UpdateCollectionViewCell(UIIndexPath indexPath)
        {
            var cell = ActualView.GetCollectionViewCell(indexPath);
            if (cell == null) return;

            var cellView = cell as ExchangeShopCell;
            if(cellView == null) return;

            var model = _viewModel.CellViewModels[indexPath.Row];
            cellView.Setup(model);
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // 不要
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            var cell = _viewModel.CellViewModels[indexPath.Row];

            if (identifier == "itemDetail")
            {
                ViewDelegate.OnItemIconButtonTapped(cell.PlayerResourceIconViewModel);
            }
            else if (identifier == "purchase")
            {
                ViewDelegate.ShowTradeConfirmView(
                    cell.MstExchangeShopId,
                    cell.MstLineupId,
                    cell.PlayerResourceIconViewModel,
                    indexPath);
            }
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.CellViewModels.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ExchangeShopCell>();
            var model = _viewModel.CellViewModels[indexPath.Row];
            cell.Setup(model);
            return cell;
        }

        [UIAction]
        public void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
