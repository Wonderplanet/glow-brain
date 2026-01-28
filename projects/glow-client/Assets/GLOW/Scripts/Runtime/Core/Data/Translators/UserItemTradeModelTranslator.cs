using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserItemTradeModelTranslator
    {
        public static UserItemTradeModel ToUserItemTradeModel(UsrItemTradeData data)
        {
            if(data == null) return UserItemTradeModel.Empty;
            
            return new UserItemTradeModel(
                new MasterDataId(data.MstItemId),
                new TradableAmount(data.TradeAmount),
                new TradeAmountResetAt(data.TradeAmountResetAt));
        }
    }
}