using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Shop
{
    public record PurchaseResultCacheModel(
        IReadOnlyList<RewardModel> Rewards,
        UserStoreProductModel UserStoreProductModel)
    {
        public static PurchaseResultCacheModel Empty { get; } = new(
            new List<RewardModel>(),
            UserStoreProductModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
