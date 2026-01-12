using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.Models;
using Zenject;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.UseCases
{
    public class GetOutpostArtworkChangeConfirmUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public OutpostArtworkChangeConfirmModel GetChangeArtworkPath(MasterDataId changeTargetMstArtworkId)
        {
            var beforeMstArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();
            var beforeMstArtwork = MstArtworkDataRepository.GetArtwork(beforeMstArtworkId);
            var afterMstArtwork = MstArtworkDataRepository.GetArtwork(changeTargetMstArtworkId);
            return new OutpostArtworkChangeConfirmModel(
                ArtworkAssetPath.CreateSmall(beforeMstArtwork.AssetKey),
                ArtworkAssetPath.CreateSmall(afterMstArtwork.AssetKey)
                );
        }
    }
}
