using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using GLOW.Scenes.TradeShop.Presentation.View;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopConfirmViewController : UIViewController<ExchangeShopConfirmView>
    {
        public record Argument(
            MasterDataId MstExchangeId,
            MasterDataId MstExchangeLineupId,
            ExchangeConfirmViewModel ExchangeConfirmViewModel,
            Action OnTradeCompleted,
            PlayerResourceIconViewModel TradeIconViewModel);

        [Inject] IExchangeConfirmViewDelegate ViewDelegate { get; }

        public ItemAmount SelectedTradeAmount => ActualView.SelectedTradeAmount;

        PlayerResourceIconViewModel _tradeIconViewModel;
        Action _onExchangeCompleted;

        ExchangeConfirmViewModel _exchangeConfirmViewModel;
        ItemAmount _decreasedOfferItemAmount;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpView(
            ExchangeConfirmViewModel viewModel,
            Action onTradeCompleted,
            PlayerResourceIconViewModel tradeIconViewModel)
        {
            _exchangeConfirmViewModel = viewModel;
            _onExchangeCompleted = onTradeCompleted;
            _tradeIconViewModel = tradeIconViewModel;
            _decreasedOfferItemAmount = viewModel.CurrentCostItemAmount;

            ActualView.Setup(viewModel, UpdateView);
            UpdateView();
        }

        void UpdateView()
        {
            // 消費するアイテム数
            var costAmount = ActualView.SelectedTradeAmount * _exchangeConfirmViewModel.CostItemAmount;

            // 確認テキスト
            UpdateConfirmText(costAmount);

            // 交換アイテムアイコン
            UpdateTradeItemIcon();

            // 消費アイテム所持数表示
            UpdateCostAmountText(costAmount);

            // 交換ボタンのグレーアウト
            UpdateTradeButtonGrayOut();

            // 交換上限数表示
            UpdatePurchaseAmountText();
        }

        void UpdatePurchaseAmountText()
        {
            // 交換可能数が無制限の場合は更新する必要ないのでスキップ
            if (_exchangeConfirmViewModel.MaxPurchaseCount.IsInfinity()) return;

            var purchaseCount = _exchangeConfirmViewModel.MaxPurchaseCount -
                                new PurchaseCount(ActualView.SelectedTradeAmount.Value);
            ActualView.SetPurchaseAmountText(purchaseCount);
        }

        void UpdateTradeButtonGrayOut()
        {
            ActualView.SetTradeButtonGrayOut(_decreasedOfferItemAmount.IsMinus());
        }

        void UpdateCostAmountText(ItemAmount itemAmount)
        {
            _decreasedOfferItemAmount = _exchangeConfirmViewModel.CurrentCostItemAmount - itemAmount;
            ActualView.SetCostAmountText(_exchangeConfirmViewModel.CurrentCostItemAmount, _decreasedOfferItemAmount);
        }

        void UpdateConfirmText(ItemAmount itemAmount)
        {
            ActualView.SetConfirmText(
                _exchangeConfirmViewModel.CostItemName,
                itemAmount,
                _exchangeConfirmViewModel.ExchangeItemName);
        }

        void UpdateTradeItemIcon()
        {
            var tradeItemAmount = _exchangeConfirmViewModel.ExchangeItemAmount * ActualView.SelectedTradeAmount;
            var itemIconViewModel = _exchangeConfirmViewModel.ExchangeItemIconViewModel with
            {
                Amount = tradeItemAmount
            };
            ActualView.SetUpTradeItemIcon(itemIconViewModel);
        }

        [UIAction]
        void OnApplyButtonTapped()
        {
            ViewDelegate.OnTradeApply(_onExchangeCompleted);
        }

        [UIAction]
        void OnItemIconTapped()
        {
            ViewDelegate.ShowItemDetail(_tradeIconViewModel);
        }

        [UIAction]
        void OnCancelButtonTapped()
        {
            Dismiss();
        }
    }
}
