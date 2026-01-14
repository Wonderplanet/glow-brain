using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstExchangeShopDataRepository
    {
        IReadOnlyList<MstTradeProductModel> GetTradeProducts(MasterDataId mstGroupId);
        MstTradeProductModel GetTradeProduct(MasterDataId mstExchangeId);
        MstExchangeLineupModel GetTradeLineup(MasterDataId mstLineupId);
        MstExchangeModel GetTradeContentFirstOrDefault(MasterDataId mstTradeShopId);
        IReadOnlyList<MstExchangeModel> GetTradeContents();
    }
}
