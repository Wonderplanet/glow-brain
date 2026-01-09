using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IExchangeService
    {
        UniTask<ExchangeTradeResultModel> Trade(
            CancellationToken cancellationToken,
            MasterDataId mstExchangeId,
            MasterDataId mstExchangeLineupId,
            ItemAmount amount);
    }
}
