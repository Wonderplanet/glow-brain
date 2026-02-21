using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.TradeShop.Presentation.View;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeContentTopViewController :
        UIViewController<ExchangeContentTopView>
        ,IUICollectionViewDelegate
        ,IUICollectionViewDataSource
    {
        [Inject] IExchangeContentTopViewDelegate ViewDelegate;

        ExchangeContentTopViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            ViewDelegate.OnViewWillAppear();
        }

        public void InitializeCollectionView()
        {
            ActualView.InitializeView(this, this);
        }

        public void SetUpView(ExchangeContentTopViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.ReloadData();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // Cellをタップしたときの処理
            var viewModel = _viewModel.CellViewModels[indexPath.Row];
            if(!ViewDelegate.IsOpeningExchangeShop(viewModel.EndAt))
            {
                ViewDelegate.ShowBackToHomeMessage();
                return;
            }

            ViewDelegate.ShowExchangeShop(viewModel.MstExchangeId, viewModel.TradeType);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            // 不要
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            // セクション内のアイテム数を返す
            return _viewModel?.CellViewModels.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // Cellを返す。ここでCellの初期化を行う
            var cell = collectionView.DequeueReusableCell<ExchangeContentCell>();
            var model = _viewModel.CellViewModels[indexPath.Row];
            var isOpening = ViewDelegate.IsOpeningExchangeShop(model.EndAt);

            cell.Setup(model, isOpening);

            return cell;
        }

        [UIAction]
        public void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
