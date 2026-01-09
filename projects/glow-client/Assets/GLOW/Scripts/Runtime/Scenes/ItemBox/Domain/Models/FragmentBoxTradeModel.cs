using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Domain.Models
{
    public record FragmentBoxTradeModel(
        ItemModel OfferItem,
        ItemModel ReceivedItem,
        TradableAmount TradableReceivedAmount,
        TradableAmount RemainingReceivableAmount,
        TradeCostAmount OfferFragmentAmountForOneTrade,
        ItemTradeResetType ResetType,
        RemainingTimeSpan RemainingTime)
    {
        public static FragmentBoxTradeModel Empty { get; } = new (
            ItemModel.Empty,
            ItemModel.Empty,
            TradableAmount.Empty,
            TradableAmount.Empty,
            TradeCostAmount.Empty,
            ItemTradeResetType.None,
            RemainingTimeSpan.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}