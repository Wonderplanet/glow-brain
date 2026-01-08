using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Outpost;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Domain.Definitions.Services
{
    public interface IOutpostService
    {
        UniTask<OutpostEnhanceResultModel> EnhanceOutpost(CancellationToken cancellationToken, MasterDataId enhanceId,
            OutpostEnhanceLevel nextLevel);
        UniTask<OutpostChangeArtworkResultModel> ChangeArtwork(CancellationToken cancellationToken, MasterDataId mstOutpostId, MasterDataId mstArtworkId);
    }
}
