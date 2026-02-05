using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases
{
    public class GetEncyclopediaArtworkPanelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }

        public EncyclopediaArtworkPanelModel GetArtworkPanel(MasterDataId mstArtworkId)
        {
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var isRelease = gameFetchOther.UserArtworkModels
                .Any(artwork => artwork.MstArtworkId == mstArtworkId);
            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(mstArtwork,
                gameFetchOther.UserArtworkModels, gameFetchOther.UserArtworkFragmentModels);

            return new EncyclopediaArtworkPanelModel(
                artworkPanelModel,
                isRelease ? ArtworkUnlockFlag.True : ArtworkUnlockFlag.False
            );
        }
    }
}
