using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Models
{
    public record UserStoreProductModel(MasterDataId ProductSubId, PurchaseCount PurchaseCount, PurchaseCount PurchaseTotalCount)
    {
        public static UserStoreProductModel Empty { get; } = new (MasterDataId.Empty, PurchaseCount.Empty, PurchaseCount.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
