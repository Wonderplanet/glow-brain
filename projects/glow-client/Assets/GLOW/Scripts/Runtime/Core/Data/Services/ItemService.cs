using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Item;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class ItemService : IItemService
    {
        [Inject] ItemApi ItemApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<ItemConsumeResultModel> IItemService.Consume(
            CancellationToken cancellationToken,
            MasterDataId mstItemId,
            ItemAmount amount)
        {
            try
            {
                var resultData = await ItemApi.Consume(cancellationToken, mstItemId.Value, amount.Value);
                return ItemConsumeResultDataTranslator.ToItemConsumeResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<ItemExchangeSelectItemResultModel> IItemService.ExchangeSelectItem(
            CancellationToken cancellationToken,
            MasterDataId mstItemId,
            MasterDataId selectedMstItemId,
            ItemAmount amount)
        {
            try
            {
                var resultData = await ItemApi.ExchangeSelectItem(
                    cancellationToken,
                    mstItemId.Value,
                    selectedMstItemId.Value,
                    amount.Value);

                return ItemExchangeSelectItemResultDataTranslator.ToItemExchangeSelectItemResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
