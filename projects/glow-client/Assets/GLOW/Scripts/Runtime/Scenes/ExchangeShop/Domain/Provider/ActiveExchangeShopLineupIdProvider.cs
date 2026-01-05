using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.Provider
{
    public class ActiveExchangeShopLineupIdProvider : IActiveExchangeShopLineupIdProvider
    {
        [Inject] IMstExchangeShopDataRepository MstExchangeShopDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        MasterDataId IActiveExchangeShopLineupIdProvider.GetExchangeLineupId(MasterDataId mstExchangeShopId)
        {
            var mstModel = MstExchangeShopDataRepository.GetTradeContents()
                .FirstOrDefault(m =>
                    m.Id == mstExchangeShopId &&
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now,
                        m.StartAt.Value,
                        m.EndAt.Value)
                );

            return mstModel?.MstGroupId ?? MasterDataId.Empty;
        }
    }
}

