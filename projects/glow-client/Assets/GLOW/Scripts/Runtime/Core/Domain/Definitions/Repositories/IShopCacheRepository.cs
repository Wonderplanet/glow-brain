namespace GLOW.Core.Domain.Repositories
{
    public interface IShopCacheRepository
    {
        bool IsContainShopCache { get; }
        bool IsContainStoreCache { get; }
        void SetCacheShopTabBadge(bool isContainShop, bool isContainStore);
    }
}
