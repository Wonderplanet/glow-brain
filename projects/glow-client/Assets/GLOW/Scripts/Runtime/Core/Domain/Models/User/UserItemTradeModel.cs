using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserItemTradeModel(
        MasterDataId MstItemId,
        TradableAmount TradeAmount,
        TradeAmountResetAt TradeAmountResetAt)
    {
        public static UserItemTradeModel Empty { get; } = new UserItemTradeModel(
            MasterDataId.Empty,
            TradableAmount.Empty,
            TradeAmountResetAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
            
    }
}