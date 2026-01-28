using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstItemRarityTradeModel(
        MasterDataId Id,
        Rarity Rarity,
        ItemTradeResetType TradeResetType,
        TradeCostAmount TradeCostAmount,
        TradableAmount MaxTradableAmount)
    {
        public static MstItemRarityTradeModel Empty { get; } = new MstItemRarityTradeModel(
            MasterDataId.Empty,
            Rarity.R,
            ItemTradeResetType.None,
            TradeCostAmount.Empty,
            TradableAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}