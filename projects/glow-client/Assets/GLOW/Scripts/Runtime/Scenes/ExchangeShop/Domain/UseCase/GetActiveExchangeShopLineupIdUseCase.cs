using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ExchangeShop.Domain.Provider;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    // 指定された交換所IDのLineupId(GroupId)を取得する
    public class GetActiveExchangeShopLineupIdUseCase
    {
        [Inject] IActiveExchangeShopLineupIdProvider ActiveExchangeShopLineupIdProvider { get; }

        public MasterDataId GetExchangeLineupId(MasterDataId mstExchangeShopId)
        {
            return ActiveExchangeShopLineupIdProvider.GetExchangeLineupId(mstExchangeShopId);
        }
    }
}

