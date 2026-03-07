using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class FragmentBoxTradeViewController : UIViewController<FragmentBoxTradeView>, IEscapeResponder
    {
        [Inject] IFragmentBoxTradeViewDelegate TradeViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        FragmentBoxTradeViewModel _tradeViewModel;

        ItemAmount _decreasedOfferItemAmount;
        
        public record Argument(ItemModel FragmentItemModel);
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            TradeViewDelegate.OnViewDidLoad();
        }

        public void SetUpFragmentBoxTradeView(FragmentBoxTradeViewModel tradeViewModel)
        {
            _tradeViewModel = tradeViewModel;

            ActualView.SetUpAmountSelectionComponent(_tradeViewModel, SetAmountChangeableUi);
            
            // 交換量に伴い表示変化が起きるUIのセットアップ
            SetAmountChangeableUi();
            
            // 交換元アイテム
            ActualView.SetUpOfferFragmentImage(_tradeViewModel.OfferItemViewModel.ItemIconAssetPath);
            
            ActualView.SetUpRemainingTimeSpan(_tradeViewModel.RemainingTime);
            
            ActualView.SetUpReceivedItemIconButton(() =>
            {
                ShowItemDetail(_tradeViewModel.ReceivedItemId);
            });
        }

        public void PlayShowAnimation()
        {
            ActualView.PlayShowAnimation();
        }

        public void PlayCloseAnimation()
        {
            ActualView.PlayCloseAnimation();
        }

        public void HiddenOnlyModal(bool isHidden)
        {
            ActualView.RootRect.gameObject.SetActive(!isHidden);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            TradeViewDelegate.OnCancelButtonTapped();
            return true;
        }

        void SetAmountChangeableUi()
        {
            // 交換数に伴う消費アイテム量
            var decreaseAmount = ActualView.SelectedItemAmount * _tradeViewModel.OfferFragmentAmountForOneTrade;
            
            // 交換確認文言
            ActualView.SetupTradeConfirmationText(
                _tradeViewModel.OfferItemViewModel.Name,
                decreaseAmount,
                _tradeViewModel.ReceivedItemViewModel.Name);
            
            // 交換先アイテム
            ActualView.SetupReceivedItem(_tradeViewModel.ReceivedItemViewModel, ActualView.SelectedItemAmount);
            
            _decreasedOfferItemAmount = _tradeViewModel.OfferItemViewModel.Amount - decreaseAmount;
            
            // 変動表示
            ActualView.SetupAmountTextBeforeAndAfterTrade(_tradeViewModel.OfferItemViewModel.Amount,
                _decreasedOfferItemAmount);
            
            // グレーアウト表示
            bool isGrayout = _decreasedOfferItemAmount.IsMinus() || 
                             (_tradeViewModel.TradableReceivedAmount.IsZero() && 
                              !_tradeViewModel.TradableReceivedAmount.IsEmpty());
            ActualView.SetupTradeButtonGrayout(isGrayout);
            
            
            // 残りの受け取れる上限数表示(設定された交換可能回数 - 選択した交換数)
            // マイナス値にならないようにする
            var remainingReceivableAmount = _tradeViewModel.TradableReceivedAmount.IsInfinity() ? 
                ItemAmount.Infinity : 
                ItemAmount.Max(ItemAmount.Zero, _tradeViewModel.TradableReceivedAmount - ActualView.SelectedItemAmount);
            
            ActualView.SetupRemainingReceivableAmount(remainingReceivableAmount, _tradeViewModel.ResetType);
        }
        
        void ShowItemDetail(MasterDataId itemId)
        {
            TradeViewDelegate.OnItemIconTapped(itemId);
        }
        
        [UIAction]
        void OnCancelButtonTapped()
        {
            TradeViewDelegate.OnCancelButtonTapped();
        }

        [UIAction]
        void OnExchangeButtonTapped()
        {
            var amount = ActualView.SelectedItemAmount;
            TradeViewDelegate.OnTradeButtonTapped(_tradeViewModel.OfferItemId, amount);
        }
    }
}