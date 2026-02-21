using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.BoxGacha;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class BoxGachaService : IBoxGachaService
    {
        [Inject] BoxGachaApi BoxGachaApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }
        
        async UniTask<BoxGachaInfoResultModel> IBoxGachaService.Info(CancellationToken cancellationToken, MasterDataId mstBoxGachaId)
        {
            try
            {
                var result = await BoxGachaApi.Info(cancellationToken, mstBoxGachaId.Value);
                return BoxGachaInfoResultDataTranslator.Translate(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<BoxGachaDrawResultModel> IBoxGachaService.Draw(
            CancellationToken cancellationToken, 
            MasterDataId mstBoxGachaId, 
            GachaDrawCount drawCount,
            BoxLevel currentBoxLevel)
        {
            try
            {
                var result = await BoxGachaApi.Draw(
                    cancellationToken, 
                    mstBoxGachaId.Value,
                    drawCount.Value,
                    currentBoxLevel.Value);
                return BoxGachaDrawResultDataTranslator.Translate(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<BoxGachaResetResultModel> IBoxGachaService.Reset(
            CancellationToken cancellationToken, 
            MasterDataId mstBoxGachaId, 
            BoxLevel currentBoxLevel)
        {
            try
            {
                var result = await BoxGachaApi.Reset(
                    cancellationToken, 
                    mstBoxGachaId.Value,
                    currentBoxLevel.Value);

                return BoxGachaResetResultDataTranslator.Translate(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}