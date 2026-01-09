using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.TradeShop.Presentation.View;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class FragmentTradeShopTopViewController :
        UIViewController<FragmentTradeShopTopView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [Inject] IFragmentTradeShopTopViewDelegate ViewDelegate { get; }

        FragmentTradeShopTopViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpView(FragmentTradeShopTopViewModel viewModels)
        {
            _viewModel = viewModels;
            ActualView.ReloadData();
        }

        public void InitializeView()
        {
            ActualView.Initialize(this, this);
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = _viewModel.ItemIconViewModels[indexPath.Row];
            ViewDelegate.ShowTradeConfirmView(cell.ItemId);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            // 必要なし
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.ItemIconViewModels.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ItemIconListCell>();
            var model = _viewModel.ItemIconViewModels[indexPath.Row];

            cell.Setup(model);
            return cell;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
