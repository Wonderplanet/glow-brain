using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class GetShopProductNoticeUseCase
    {
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IValidatedStoreProductRepository ValidatedStoreProductRepository { get; }
        [Inject] IShopProductCacheRepository ShopProductCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IShopCacheRepository ShopCacheRepository { get; }

        public bool GetShopProductNoticeAndSaveCache()
        {
            var displayedShopProductIdHashSet = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
            if (displayedShopProductIdHashSet.Count == 0)
                return true;

            var isContainShop = IsContainShop();
            var isContainStore = IsContainStore();
            var isReceivableFreeOrAdProduct = IsReceivableFreeOrAdProduct();

            var result = !(isContainShop && isContainStore) || isReceivableFreeOrAdProduct;
            ShopCacheRepository.SetCacheShopTabBadge(isContainShop, isContainStore);

            return result;
        }

        public bool GetShopProductNotice(bool isCheckOnlyAdOrFree)
        {
            var displayedShopProductIdHashSet = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
            if (displayedShopProductIdHashSet.Count == 0)
                return true;

            if(isCheckOnlyAdOrFree)
                return  !(ShopCacheRepository.IsContainShopCache && ShopCacheRepository.IsContainStoreCache) || IsReceivableFreeOrAdProduct();


            var isContainShop = IsContainShop();
            var isContainStore = IsContainStore();
            var isReceivableFreeOrAdProduct = IsReceivableFreeOrAdProduct();

            var result = !(isContainShop && isContainStore) || isReceivableFreeOrAdProduct;
            return result;
        }

        bool IsContainShop()
        {
            var displayedShopProductIdHashSet = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
            if (displayedShopProductIdHashSet.Count == 0)
                return true;

            var shopNotificationList =
                MstShopProductDataRepository.GetShopProducts()
                    .Where(product => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, product.StartDate, product.EndDate))
                    .Select(shopProduct =>
                    {
                        var isContain = displayedShopProductIdHashSet.Contains(shopProduct.Id);
                        // Newフラグが有効、かつ無料ではない商品はtrue
                        return isContain;
                    })
                    .ToList();
            return shopNotificationList.All(value => value);

        }

        bool IsContainStore()
        {
            var displayedShopProductIdHashSet = ShopProductCacheRepository.DisplayedShopProductIdHashSet;
            if (displayedShopProductIdHashSet.Count == 0)
                return true;

            var storeNotificationList =
                ValidatedStoreProductRepository.GetValidatedStoreProducts()
                    .Select(product => product.MstStoreProduct)
                    .Where(product => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, product.StartDate, product.EndDate))
                    .Select(storeProduct =>
                    {
                        var isContain = displayedShopProductIdHashSet.Contains(storeProduct.Id);
                        return isContain;
                    })
                    .ToList();

            return storeNotificationList.All(value => value);
        }

        bool IsReceivableFreeOrAdProduct()
        {
            var userShopItemModels = GameRepository.GetGameFetchOther().UserShopItemModels;
            var result =
                MstShopProductDataRepository.GetShopProducts()
                    .Where(product =>
                        CalculateTimeCalculator.IsValidTime(TimeProvider.Now, product.StartDate, product.EndDate))
                    .Where(p => p.CostType is CostType.Free or CostType.Ad)
                    .Any(p =>
                    {
                        var userShopItemModel = userShopItemModels.FirstOrDefault(u => u.MstShopItemId == p.Id);
                        if (userShopItemModel == null) return true;

                        return userShopItemModel.TradeCount < p.PurchasableCount;
                    });
            return result;
        }
    }
}
