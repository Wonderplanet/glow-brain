using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ExchangeShop.Domain.Provider
{
    public interface IActiveExchangeShopLineupIdProvider
    {
        MasterDataId GetExchangeLineupId(MasterDataId mstExchangeShopId);
    }
}

