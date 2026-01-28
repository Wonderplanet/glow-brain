using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserExchangeLineupModel(
        MasterDataId MstExchangeId,
        MasterDataId MstExchangeLineupId,
        ItemAmount PurchasedCount)
    {
        public static UserExchangeLineupModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ItemAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
