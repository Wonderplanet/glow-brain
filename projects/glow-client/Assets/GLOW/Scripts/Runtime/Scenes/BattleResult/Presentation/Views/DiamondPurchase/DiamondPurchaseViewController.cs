using System;
using System.Collections.Generic;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.Shop.Presentation.Component;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-3_プリズム購入画面（コンティニュー）
    /// </summary>
    public class DiamondPurchaseViewController : UIViewController<DiamondPurchaseView>, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        public record Argument(Action OnViewClosed);

        [Inject] IDiamondPurchaseViewDelegate ViewDelegate { get; }

        IReadOnlyList<ShopProductCellViewModel> _shopProductCellViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();

            ViewDelegate.OnViewDidAppear();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetShopViewModel(IReadOnlyList<ShopProductCellViewModel> shopProductCellViewModels)
        {
            _shopProductCellViewModels = shopProductCellViewModels;
        }

        public void SetupShopDisplay()
        {
            ActualView.CollectionView.ReloadData();
        }

        public void UpdatePurchasedProductUi(UIIndexPath indexPath)
        {
            var cell = ActualView.CollectionView.CellForRow(indexPath);
            if (cell == null)
            {
                return;
            }

            var cellView = cell as ShopCellView;
            if(cellView == null)
            {
                return;
            }

            var viewModel = _shopProductCellViewModels[indexPath.Row];
            cellView.Setup(viewModel, HeldAdSkipPassInfoViewModel.Empty, true);
        }

        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _shopProductCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ShopCellView>();
            var viewModel = _shopProductCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;

            cell.Setup(viewModel, HeldAdSkipPassInfoViewModel.Empty);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {

        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            string buttonKey = identifier.ToString();
            var viewModel = _shopProductCellViewModels[indexPath.Row];
            switch (buttonKey)
            {
                case ShopCellView.InfoButtonKey:
                    ViewDelegate.OnItemIconSelected(viewModel);
                    break;
                case ShopCellView.PurchaseButtonKey:
                    ViewDelegate.OnPurchaseButtonTapped(viewModel, indexPath);
                    break;
                case ShopCellView.ItemIconKey:
                    ViewDelegate.OnProductInfoSelected(viewModel);
                    break;
            }
        }

        [UIAction]
        void OnSpecificCommerceButtonTapped()
        {
            ViewDelegate.OnSpecificCommerceSelected();
        }

        [UIAction]
        void OnFundsSettlementButtonTapped()
        {
            ViewDelegate.OnFundsSettlementSelected();
        }

        [UIAction]
        void OnCloseTapped()
        {
            ViewDelegate.OnCloseSelected();
        }

        [UIAction]
        void OnDiamondPurchaseHistoryButtonTapped()
        {
            ViewDelegate.ShowDiamondPurchaseHistory();
        }
    }
}
