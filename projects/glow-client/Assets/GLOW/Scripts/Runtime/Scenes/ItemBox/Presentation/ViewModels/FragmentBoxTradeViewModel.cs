using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record FragmentBoxTradeViewModel(
        ItemDetailViewModel OfferItemViewModel,
        ItemDetailViewModel ReceivedItemViewModel,
        MasterDataId OfferItemId,
        MasterDataId ReceivedItemId,
        ItemAmount TradableReceivedAmount,
        ItemAmount RemainingReceivableAmount,
        ItemAmount OfferFragmentAmountForOneTrade,
        ItemTradeResetType ResetType,
        RemainingTimeSpan RemainingTime);
}