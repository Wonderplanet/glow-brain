using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Item;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IItemService
    {
        UniTask<ItemConsumeResultModel> Consume(CancellationToken cancellationToken, MasterDataId mstItemId, ItemAmount amount);

        UniTask<ItemExchangeSelectItemResultModel> ExchangeSelectItem(
            CancellationToken cancellationToken,
            MasterDataId mstItemId,
            MasterDataId selectedMstItemId,
            ItemAmount amount);
    }
}
