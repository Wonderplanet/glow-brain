using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkExpandDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Domain.UseCases
{
    public class GetArtworkExpandUseCase
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }

        public ArtworkExpandDialogModel GetArtwork(MasterDataId mstArtworkId)
        {
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(mstArtwork,
                gameFetchOther.UserArtworkModels, gameFetchOther.UserArtworkFragmentModels);
            var artworkCompleted = gameFetchOther.UserArtworkModels
                .Any(model => model.MstArtworkId == mstArtworkId);

            return new ArtworkExpandDialogModel(
                mstArtwork.Name,
                mstArtwork.Description,
                ArtworkAssetPath.Create(mstArtwork.AssetKey),
                artworkCompleted ? ArtworkCompletedFlag.True : ArtworkCompletedFlag.False,
                artworkPanelModel.ArtworkFragmentModels
                );
        }
    }
}
