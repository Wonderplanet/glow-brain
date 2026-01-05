using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstItemRarityTradeModelTranslator
    {
        public static MstItemRarityTradeModel ToItemRarityTradeModel(MstItemRarityTradeData data)
        {
            return new MstItemRarityTradeModel(
                new MasterDataId(data.Id),
                data.Rarity,
                data.ResetType,
                new TradeCostAmount(data.CostAmount),
                (data.MaxTradableAmount == null) ? TradableAmount.Infinity : new TradableAmount(data.MaxTradableAmount.Value));
        }
    }
}