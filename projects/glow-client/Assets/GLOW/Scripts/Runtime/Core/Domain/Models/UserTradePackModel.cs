using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record UserTradePackModel(
        MasterDataId MstPackId,
        PurchaseCount DailyTradeCount)
    {
        public static UserTradePackModel Empty { get; } = new (
            MasterDataId.Empty,
            PurchaseCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
