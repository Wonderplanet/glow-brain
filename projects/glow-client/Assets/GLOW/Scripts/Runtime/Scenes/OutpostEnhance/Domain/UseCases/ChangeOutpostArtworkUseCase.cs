using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class ChangeOutpostArtworkUseCase
    {
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public void ChangeArtwork(MasterDataId mstArtworkId)
        {
            OutpostArtworkCacheRepository.SetSelectedArtwork(mstArtworkId);
        }
    }
}
