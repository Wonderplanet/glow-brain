using GLOW.Core.Domain.Repositories;

namespace GLOW.Core.Data.Repositories
{
    public class ShopCacheRepository : IShopCacheRepository
    {
        bool _isContainShop;
        bool _isContainStore;


        bool IShopCacheRepository.IsContainShopCache => _isContainShop;
        bool IShopCacheRepository.IsContainStoreCache => _isContainStore;
        void IShopCacheRepository.SetCacheShopTabBadge(bool isContainShop, bool isContainStore)
        {
            _isContainShop = isContainShop;
            _isContainStore = isContainStore;
        }
    }
}
