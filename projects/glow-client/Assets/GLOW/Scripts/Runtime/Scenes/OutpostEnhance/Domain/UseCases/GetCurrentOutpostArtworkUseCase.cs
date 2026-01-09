using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class GetCurrentOutpostArtworkUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public ArtworkAssetPath GetArtworkPath()
        {
            var mstArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            return ArtworkAssetPath.CreateSmall(mstArtwork.AssetKey);
        }

        public MasterDataId GetMstArtworkId()
        {
            return OutpostArtworkCacheRepository.GetSelectedArtwork();
        }
    }
}
