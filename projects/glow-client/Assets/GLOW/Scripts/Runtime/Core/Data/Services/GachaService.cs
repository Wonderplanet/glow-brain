using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class GachaService : IGachaService
    {
        [Inject] GachaApi GachaApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<GachaDrawResultModel> IGachaService.DrawByAd(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount)
        {
            try
            {
                var resultData = await GachaApi.Ad(cancellationToken, oprGachaId.Value, playedCount.Value);
                var gachaDrawResultServiceModel = Translate(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaPrizeResultModel> IGachaService.Prize(CancellationToken cancellationToken, MasterDataId oprGachaId)
        {
            try
            {
                var resultData = await GachaApi.Prize(cancellationToken, oprGachaId.Value);
                var gachaPrizeResultServiceModel = GachaPrizeResultDataTranslator.ToGachaDrawResultModel(resultData);
                return gachaPrizeResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaDrawResultModel> IGachaService.DrawByDiamond(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, CostAmount costAmount)
        {
            try
            {
                var resultData = await GachaApi.Diamond(cancellationToken, oprGachaId.Value, playedCount.Value, drawCount.Value, (int)costAmount.Value);
                var gachaDrawResultServiceModel = Translate(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaDrawResultModel> IGachaService.DrawByPaidDiamond(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, CostAmount costAmount)
        {
            try
            {
                var resultData = await GachaApi.PaidDiamond(cancellationToken, oprGachaId.Value, playedCount.Value, drawCount.Value, (int)costAmount.Value);
                var gachaDrawResultServiceModel = Translate(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaDrawResultModel> IGachaService.DrawByItem(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, MasterDataId costId,CostAmount costAmount)
        {
            try
            {
                var resultData = await GachaApi.Item(cancellationToken, oprGachaId.Value, playedCount.Value, drawCount.Value, costId.Value, (int)costAmount.Value);
                var gachaDrawResultServiceModel = Translate(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaDrawResultModel> IGachaService.DrawByFree(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount)
        {
            try
            {
                var resultData = await GachaApi.Free(cancellationToken, oprGachaId.Value, playedCount.Value);
                var gachaDrawResultServiceModel = Translate(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<GachaHistoryResultModel> IGachaService.History(CancellationToken cancellationToken)
        {
            try
            {
                var resultData = await GachaApi.History(cancellationToken);
                var gachaDrawResultServiceModel = GachaHistoryResultDataTranslator.ToGachaDrawResultModel(resultData);
                return gachaDrawResultServiceModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        static GachaDrawResultModel Translate(GachaDrawResultData resultData)
        {
            return GachaDrawResultDataTranslator.ToGachaDrawResultModel(resultData);
        }
    }
}
