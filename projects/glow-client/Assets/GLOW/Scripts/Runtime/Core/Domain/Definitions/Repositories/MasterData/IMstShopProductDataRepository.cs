using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstShopProductDataRepository
    {
        IReadOnlyList<MstShopItemModel> GetShopProducts();
        IReadOnlyList<MstStoreProductModel> GetStoreProducts();
        IReadOnlyList<MstPackModel> GetPacks();
        IReadOnlyList<MstPackContentModel> GetPackContents(MasterDataId id);
        IReadOnlyList<MstShopPassModel> GetShopPasses();
        MstShopPassModel GetShopPass(MasterDataId mstShopPassId);
        IReadOnlyList<MstShopPassEffectModel> GetShopPassEffects(MasterDataId mstShopPassId);
        IReadOnlyList<MstShopPassRewardModel> GetShopPassRewards(MasterDataId mstShopPassId);
    }
}
