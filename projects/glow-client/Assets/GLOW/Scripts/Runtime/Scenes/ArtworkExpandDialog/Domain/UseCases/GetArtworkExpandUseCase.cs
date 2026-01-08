using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkExpandDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Domain.UseCases
{
    public class GetArtworkExpandUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        public ArtworkExpandDialogModel GetArtwork(MasterDataId mstArtworkId)
        {
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            return new ArtworkExpandDialogModel(
                mstArtwork.Name,
                mstArtwork.Description,
                ArtworkAssetPath.Create(mstArtwork.AssetKey)
                );
        }
    }
}
