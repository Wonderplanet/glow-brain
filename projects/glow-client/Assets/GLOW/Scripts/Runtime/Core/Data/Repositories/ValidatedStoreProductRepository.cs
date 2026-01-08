using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Core.Data.Repositories
{
    public class ValidatedStoreProductRepository : IValidatedStoreProductRepository
    {
        IReadOnlyList<ValidatedStoreProductModel> _cache = new List<ValidatedStoreProductModel>();

        void IValidatedStoreProductRepository.Register(IReadOnlyList<ValidatedStoreProductModel> mstStoreProducts)
        {
            _cache = mstStoreProducts;
        }

        IReadOnlyList<ValidatedStoreProductModel> IValidatedStoreProductRepository.GetValidatedStoreProducts()
        {
            return _cache;
        }
    }
}
