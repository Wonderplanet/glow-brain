using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.UseCases
{
    public class ChangeOutpostArtworkUseCase
    {
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public void ChangeOutpostArtwork(MasterDataId mstArtworkId)
        {
            OutpostArtworkCacheRepository.SetSelectedArtwork(mstArtworkId);
        }
    }
}
