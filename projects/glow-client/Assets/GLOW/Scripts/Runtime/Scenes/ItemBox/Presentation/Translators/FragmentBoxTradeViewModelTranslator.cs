using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ItemBox.Domain.Models;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;

namespace GLOW.Scenes.ItemBox.Presentation.Translators
{
    public class FragmentBoxTradeViewModelTranslator
    {
        public static FragmentBoxTradeViewModel ToFragmentBoxTradeViewModel(
            FragmentBoxTradeModel tradeModel)
        {
            return new FragmentBoxTradeViewModel(
                ItemViewModelTranslator.ToItemDetailViewModel(tradeModel.OfferItem),
                ItemViewModelTranslator.ToItemDetailViewModel(tradeModel.ReceivedItem),
                tradeModel.OfferItem.Id,
                tradeModel.ReceivedItem.Id,
                tradeModel.TradableReceivedAmount.IsInfinity() 
                    ? ItemAmount.Infinity 
                    : tradeModel.TradableReceivedAmount.ToItemAmount(),
                tradeModel.RemainingReceivableAmount.ToItemAmount(),
                tradeModel.OfferFragmentAmountForOneTrade.ToItemAmount(),
                tradeModel.ResetType,
                tradeModel.RemainingTime);
        } 
    }
}