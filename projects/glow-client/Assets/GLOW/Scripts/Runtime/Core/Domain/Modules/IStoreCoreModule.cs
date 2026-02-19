using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Modules
{
    public interface IStoreCoreModule
    {
        UniTask Initialize(CancellationToken cancellationToken, IReadOnlyList<ShopProductId> productIds);
        IReadOnlyList<ValidatedStoreProductModel> ValidateProducts(IReadOnlyList<MstStoreProductModel> mstStoreProducts);
        bool IsPurchasedProductDeferred(ShopProductId productId);
        UniTask<PurchaseResultModel> BuyProduct(CancellationToken cancellationToken, MasterDataId oprProductId);
        UniTask<PurchasePassResultModel> BuyPassProduct(CancellationToken cancellationToken, MasterDataId oprProductId);
    }
}
