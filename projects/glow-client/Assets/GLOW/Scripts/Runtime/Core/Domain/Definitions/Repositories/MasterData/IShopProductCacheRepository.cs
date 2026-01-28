using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IShopProductCacheRepository
    {
        DateTimeOffset LastCheckedNewShopProductDateTimeOffset { get; }
        void SaveLastCheckedNewShopProductDateTimeOffset(DateTimeOffset lastCheckedNewShopProductDateTimeOffset);

        HashSet<MasterDataId> DisplayedShopProductIdHashSet { get; }
        void SetDisplayedShopProductIdHashSet(HashSet<MasterDataId> productIdHashSet);
        void AddDisplayedShopProductIds(IReadOnlyCollection<MasterDataId> productIds);
        
        List<MasterDataId> DisplayedOprPackProductIds { get; set; }
    }
}