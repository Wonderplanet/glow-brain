using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Translators;
using UnityHTTPLibrary;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Services;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public sealed class GameService : IGameService
    {
        [Inject] GameApi GameApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<GameVersionModel> IGameService.FetchVersion(CancellationToken cancellationToken)
        {
            try
            {
                var versionResultData = await GameApi.Version(cancellationToken);
                return GameVersionTranslator.TranslateToModel(versionResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GameUpdateAndFetchResultModel> IGameService.UpdateAndFetch(CancellationToken cancellationToken)
        {
            try
            {
                var updateAndFetchResultData = await GameApi.UpdateAndFetch(cancellationToken);
                var fetchData = updateAndFetchResultData.Fetch;
                var fetchOther = updateAndFetchResultData.FetchOther;
                var fetchModel = GameFetchTranslator.TranslateToModel(fetchData);
                var fetchOtherModel = GameFetchOtherTranslator.TranslateToModel(fetchOther);
                return new GameUpdateAndFetchResultModel(fetchModel, fetchOtherModel);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GameFetchResultModel> IGameService.Fetch(CancellationToken cancellationToken)
        {
            try
            {
                var fetchResultData = await GameApi.Fetch(cancellationToken);
                var fetchData = fetchResultData.Fetch;
                var fetchModel = GameFetchTranslator.TranslateToModel(fetchData);
                return new GameFetchResultModel(fetchModel);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GameBadgeResultModel> IGameService.Badge(CancellationToken cancellationToken)
        {
            try
            {
                var resultData = await GameApi.Badge(cancellationToken);
                return GameBadgeResultTranslator.ToGameBadgeResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GameServerTimeModel> IGameService.FetchServerTime(CancellationToken cancellationToken)
        {
            try
            {
                var serverTimeResultData = await GameApi.ServerTime(cancellationToken);
                return new GameServerTimeModel(serverTimeResultData.ServerTime);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
