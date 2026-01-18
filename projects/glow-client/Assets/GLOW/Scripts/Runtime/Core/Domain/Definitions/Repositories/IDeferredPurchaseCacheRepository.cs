using System.Collections.Generic;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Repositories
{
    public interface IDeferredPurchaseCacheRepository
    {
        void AddDeferredPurchaseResult(PurchaseResultCacheModel model);
        IReadOnlyList<PurchaseResultCacheModel> GetAndResetDeferredPurchaseResults();

        void AddRestorePurchaseResult(PurchaseResultCacheModel model);
        IReadOnlyList<PurchaseResultCacheModel> GetAndResetRestorePurchaseResults();
        void AddDeferredPurchaseErrorCode(DeferredPurchaseErrorCode errorCode);
        IReadOnlyList<DeferredPurchaseErrorCode> GetAndResetDeferredPurchaseErrorCode();
    }
}
