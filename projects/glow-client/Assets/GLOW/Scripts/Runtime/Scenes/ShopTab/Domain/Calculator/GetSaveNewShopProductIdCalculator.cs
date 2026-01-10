using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ShopTab.Domain.Calculator
{
    public class GetSaveNewShopProductIdCalculator
    {
        public static IReadOnlyList<MasterDataId> GetShopSavedProductIdSet(
            IMstShopProductDataRepository mstShopProductDataRepository,
            IShopProductCacheRepository shopProductCacheRepository, 
            DateTimeOffset nowTime)
        {
            var displayedShopProductIdHashSet = shopProductCacheRepository.DisplayedShopProductIdHashSet;
            var savedShopProductIdSet = mstShopProductDataRepository.GetShopProducts()
                .Where(product => CalculateTimeCalculator.IsValidTime(nowTime, product.StartDate, product.EndDate))
                .Where(model => !model.IsEmpty())
                .Select(
                    shopProduct =>
                    {
                        var isContain = displayedShopProductIdHashSet.Contains(shopProduct.Id);
                        return isContain ? MasterDataId.Empty : shopProduct.Id;
                    })
                .Distinct()
                .ToList();

            return savedShopProductIdSet;
        }

        public static IReadOnlyList<MasterDataId> GetStoreSavedProductIdSet(
            IValidatedStoreProductRepository validatedStoreProductRepository,
            IShopProductCacheRepository shopProductCacheRepository, 
            DateTimeOffset nowTime)
        {
            var displayedShopProductIdHashSet = shopProductCacheRepository.DisplayedShopProductIdHashSet;
            var savedStoreProductIdSet = validatedStoreProductRepository.GetValidatedStoreProducts()
                .Select(product => product.MstStoreProduct)
                .Where(product => CalculateTimeCalculator.IsValidTime(nowTime, product.StartDate, product.EndDate))
                .Where(model => !model.IsEmpty())
                .Select(
                    storeProduct =>
                    {
                        var isContain = displayedShopProductIdHashSet.Contains(storeProduct.Id);
                        return isContain ? MasterDataId.Empty : storeProduct.Id;
                    })
                .Distinct()
                .ToList();

            return savedStoreProductIdSet;
        }
    }
}
