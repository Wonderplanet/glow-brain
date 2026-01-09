using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Domain.Repositories
{
    public interface IValidatedStoreProductRepository
    {
        void Register(IReadOnlyList<ValidatedStoreProductModel> mstStoreProducts);
        IReadOnlyList<ValidatedStoreProductModel> GetValidatedStoreProducts();
    }
}
