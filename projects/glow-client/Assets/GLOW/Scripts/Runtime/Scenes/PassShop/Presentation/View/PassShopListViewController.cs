using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.Component;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PassShop.Presentation.View
{
    public class PassShopListViewController : UIViewController<PassShopListView>
    {
        [Inject] IPassShopListViewDelegate ViewDelegate { get; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void SetupPassProductList(IReadOnlyList<PassShopProductViewModel> viewModels)
        {
            for (int i = 0; i < viewModels.Count; i++)
            {
                var viewModel = viewModels[i];
                var passListCell = ActualView.InstantiatePassShopProductListCell(viewModel.ShopPassCellColor);
                SetUpPassProductCell(passListCell, viewModel);
            }
        }

        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        public void RemoveAllPassShopProductListCells()
        {
            ActualView.RemoveAllPassShopProductListCells();
        }

        void SetUpPassProductCell(PassShopProductListCell cell, PassShopProductViewModel viewModel)
        {
            cell.LoadPassImage(viewModel.PassIconAssetPath);
            cell.SetTitleText(viewModel.PassProductName);
            cell.SetStartDateText(viewModel.PassStartAt);
            cell.SetEndDateText(viewModel.PassEndAt);
            cell.SetDisplayExpirationVisible(viewModel.IsDisplayExpiration);
            cell.SetPassDescriptionText(viewModel.PassDurationDay);
            cell.SetupPassEffectListComponent(viewModel.PassEffectViewModels);
            cell.SetupPassImmediatelyRewardsComponent(
                viewModel.PassDurationDay,
                viewModel.PassImmediatelyRewardViewModels,
                OnItemIconSelected);
            cell.SetupPassDailyRewardsComponent(
                viewModel.PassDurationDay,
                viewModel.PassDailyRewardViewModels,
                OnItemIconSelected);
            SetUpCellRemainingTime(cell, viewModel.RemainingTimeSpan);
            cell.SetNoticeBadgeVisible(viewModel.RemainingTimeSpan.IsEmpty());
            cell.SetPriceText(viewModel.RawProductPriceText);
            cell.OnInfoButtonClicked.AddListener(() =>
            {
                OnInfoButtonSelected(viewModel.MstShopPassId);
            });
            cell.OnPurchaseButtonClicked.AddListener(() =>
            {
                OnPurchaseButtonSelected(viewModel.MstShopPassId, cell);
            });
        }

        void SetUpCellRemainingTime(
            PassShopProductListCell passListCell,
            RemainingTimeSpan remainingTimeSpan)
        {
            passListCell.SetPurchaseButtonVisible(remainingTimeSpan.IsEmpty());
            passListCell.SetRemainingTimeText(remainingTimeSpan);

            if (remainingTimeSpan.IsEmpty()) return;

            passListCell.StartRemainingTimeCountDown(remainingTimeSpan);
        }

        void OnInfoButtonSelected(MasterDataId mstShopPassId)
        {
            ViewDelegate.OnInfoButtonSelected(mstShopPassId);
        }

        void OnPurchaseButtonSelected(
            MasterDataId mstShopPassId,
            PassShopProductListCell passListCell)
        {
            ViewDelegate.OnPurchaseButtonSelected(mstShopPassId, (remainingTimeSpan) =>
            {
                SetUpCellRemainingTime(passListCell, remainingTimeSpan);
                passListCell.SetNoticeBadgeVisible(remainingTimeSpan.IsEmpty());
            });
        }

        void OnItemIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnItemIconSelected(viewModel);
        }
    }
}
