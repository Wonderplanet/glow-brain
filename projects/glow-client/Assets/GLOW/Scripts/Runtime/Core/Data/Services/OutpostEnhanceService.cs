using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Outpost;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.OutpostEnhance.Domain.Definitions.Services;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class OutpostService : IOutpostService
    {
        [Inject] OutpostApi OutpostApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }
        public async UniTask<OutpostEnhanceResultModel> EnhanceOutpost(CancellationToken cancellationToken, MasterDataId enhanceId, OutpostEnhanceLevel nextLevel)
        {
            try
            {
                var outpostEnhanceResultData = await OutpostApi.Enhance(cancellationToken, enhanceId.Value, nextLevel.Value);
                return OutpostEnhanceResultDataTranslator.ToOutpostEnhanceResultModel(outpostEnhanceResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        public async UniTask<OutpostChangeArtworkResultModel> ChangeArtwork(CancellationToken cancellationToken,
            MasterDataId mstOutpostId, MasterDataId mstArtworkId)
        {
            try
            {
                var result = await OutpostApi.ChangeArtwork(cancellationToken, mstOutpostId.Value, mstArtworkId.Value);
                return OutpostEnhanceResultDataTranslator.ToOutpostChangeArtworkResultModel(result);
            }
            catch (ServerErrorException e)
            {
                throw ServerErrorExceptionMapper.Map(e);
            }
        }

    }
}
