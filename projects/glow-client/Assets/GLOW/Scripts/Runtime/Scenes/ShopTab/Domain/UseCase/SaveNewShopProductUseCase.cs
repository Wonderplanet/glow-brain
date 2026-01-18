using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ShopTab.Domain.Calculator;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class SaveNewShopProductUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IShopProductCacheRepository ProductCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }


        public void SaveShopNewProductContain()
        {
            var nowTime = TimeProvider.Now;
            var savedShopList = GetSaveNewShopProductIdCalculator.GetShopSavedProductIdSet(
                MstShopProductDataRepository, 
                ProductCacheRepository,
                nowTime);

            var savedStoreList = GetSaveNewShopProductIdCalculator.GetStoreSavedProductIdSet(
                ValidatedStoreProductRepository,
                ProductCacheRepository,
                nowTime);

            UpdateDisplayedShopProductIds(savedShopList, savedStoreList);
        }

        void UpdateDisplayedShopProductIds(IReadOnlyList<MasterDataId> shopIds, IReadOnlyList<MasterDataId> storeIds)
        {
            if (shopIds.IsEmpty() && storeIds.IsEmpty()) return;

            var saveIds = shopIds.Concat(storeIds).ToList();
            ProductCacheRepository.AddDisplayedShopProductIds(saveIds);;
        }
    }
}
