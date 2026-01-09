using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class ExchangeService : IExchangeService
    {
        [Inject] ExchangeApi ExchangeApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<ExchangeTradeResultModel> IExchangeService.Trade(
            CancellationToken cancellationToken,
            MasterDataId mstExchangeId,
            MasterDataId mstExchangeLineupId,
            ItemAmount amount)
        {
            try
            {
                var resultModel = await ExchangeApi.Trade(
                    cancellationToken,
                    mstExchangeId.Value,
                    mstExchangeLineupId.Value,
                    amount.Value);
                return ExchangeTradeResultDataTranslator.Translate(resultModel);
            }
            catch (ServerErrorException e)
            {
                throw ServerErrorExceptionMapper.Map(e);
            }
        }
    }
}
