using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Shop.Presentation.Component;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCollectionViewController :
        HomeBaseViewController<ShopCollectionView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [Inject] IShopCollectionViewDelegate ViewDelegate { get; }

        const int MaxSection = 4;
        const int DiamondSection = 0;
        const int DailySection = 1;
        const int WeeklySection = 2;
        const int CoinSection = 3;

        ShopViewModel _shopViewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            ViewDelegate.ViewDidDisappear();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            ActualView.CollectionScrollToTop();
            ViewDelegate.ViewWillAppear();
        }

        public void SetShopViewModel(ShopViewModel model)
        {
            _shopViewModel = model;
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

            var viewModel = GetShopProductCellViewModel(indexPath);
            cellView.Setup(viewModel, _shopViewModel.HeldAdSkipPassInfoViewModel, true);
        }

        public void MoveToDiamondSection()
        {
            ActualView.MoveToDiamondSection();
        }

        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            switch (section)
            {
                case DiamondSection:
                    return _shopViewModel.DiamondCategory.ShopProductCellViewModels.Count;
                case DailySection:
                    return _shopViewModel.DailyCategory.ShopProductCellViewModels.Count;
                case WeeklySection:
                    return _shopViewModel.WeeklyCategory.ShopProductCellViewModels.Count;
                case CoinSection:
                    return _shopViewModel.CoinCategory.ShopProductCellViewModels.Count;
                default:
                    return 0;
            }
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<ShopCellView>();
            var cellViewModel = GetShopProductCellViewModel(indexPath);
            cell.Setup(cellViewModel, _shopViewModel.HeldAdSkipPassInfoViewModel);
            return cell;
        }

        int IUICollectionViewDataSource.NumberOfSection()
        {
            return MaxSection;
        }

        bool IUICollectionViewDataSource.IsUseSectionHeaderOfSectionIndex(int section)
        {
            return true;
        }

        UICollectionViewSectionHeader IUICollectionViewDataSource.SectionHeaderOfSectionIndex(
            UICollectionView collectionView, int section)
        {
            var header = collectionView.DequeueReusableHeader<ShopSectionHeaderView>();
            switch (section)
            {
                case DiamondSection:
                    header.SetupShopSection(
                        DisplayShopProductType.Diamond,
                        RemainingTimeSpan.Empty,
                        () => ViewDelegate.OnPurchaseHistoryButtonTapped());
                    break;
                case DailySection:
                    header.SetupShopSection(DisplayShopProductType.Daily, _shopViewModel.DailyCategory.UpdateTime);
                    break;
                case WeeklySection:
                    header.SetupShopSection(DisplayShopProductType.Weekly, _shopViewModel.WeeklyCategory.UpdateTime);
                    break;
                case CoinSection:
                    header.SetupShopSection(DisplayShopProductType.Coin, _shopViewModel.CoinCategory.UpdateTime);
                    break;
            }
            return header;
        }


        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {

        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            string buttonKey = identifier.ToString();
            var cellViewModel = GetShopProductCellViewModel(indexPath);
            switch (buttonKey)
            {
                case ShopCellView.InfoButtonKey:
                    ViewDelegate.ShowShopProductInfo(cellViewModel);
                    break;
                case ShopCellView.PurchaseButtonKey:
                    ViewDelegate.OnPurchaseButtonTapped(cellViewModel, indexPath);
                    break;
                case ShopCellView.ItemIconKey:
                    ViewDelegate.OnItemIconTapped(cellViewModel);
                    break;
            }
        }

        ShopProductCellViewModel GetShopProductCellViewModel(UIIndexPath indexPath)
        {
            switch (indexPath.Section)
            {
                case DiamondSection:
                    return _shopViewModel.DiamondCategory.ShopProductCellViewModels[indexPath.Row];
                case DailySection:
                    return _shopViewModel.DailyCategory.ShopProductCellViewModels[indexPath.Row];
                case WeeklySection:
                    return _shopViewModel.WeeklyCategory.ShopProductCellViewModels[indexPath.Row];
                case CoinSection:
                    return _shopViewModel.CoinCategory.ShopProductCellViewModels[indexPath.Row];
                default:
                    throw new ArgumentException("Invalid section");
            }
        }
    }
}
