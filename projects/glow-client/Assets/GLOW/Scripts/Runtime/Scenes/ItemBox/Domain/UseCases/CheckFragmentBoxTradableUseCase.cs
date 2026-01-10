using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Domain.Factory;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class CheckFragmentBoxTradableUseCase
    {
        [Inject] IFragmentBoxTradeModelFactory FragmentBoxTradeModelFactory { get; }
        
        public TradeStatus EvaluateFragmentBoxTradableStatus(MasterDataId offerItemId, ItemAmount receiveItemAmount)
        {
            var tradeModel = FragmentBoxTradeModelFactory.CreateFragmentBoxTradeModel(offerItemId);
            
            if (!tradeModel.TradableReceivedAmount.IsInfinity() && 
                tradeModel.TradableReceivedAmount < receiveItemAmount)
            {
                return TradeStatus.TradeLimit;
            }
            
            if (tradeModel.OfferItem.Amount < receiveItemAmount * tradeModel.OfferFragmentAmountForOneTrade)
            {
                return TradeStatus.ShortageFragment;
            }

            return TradeStatus.Tradable;
        }
    }
}