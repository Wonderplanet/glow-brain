using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases
{
    public class GetEncyclopediaArtworkPanelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }
        [Inject] IMstConfigRepository ConfigRepository { get; }

        public EncyclopediaArtworkPanelModel GetArtworkPanel(MasterDataId mstArtworkId)
        {
            var mstArtwork = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var isRelease = gameFetchOther.UserArtworkModels
                .Any(artwork => artwork.MstArtworkId == mstArtworkId);
            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(mstArtwork,
                gameFetchOther.UserArtworkModels, gameFetchOther.UserArtworkFragmentModels);

            var isGradeMax = gameFetchOther.UserArtworkModels
                .FirstOrDefault(artwork => artwork.MstArtworkId == mstArtworkId, UserArtworkModel.Empty)
                .Grade >= ConfigRepository.GetConfig(MstConfigKey.ArtworkGradeCap).Value.ToInt();

            return new EncyclopediaArtworkPanelModel(
                artworkPanelModel,
                isRelease ? ArtworkUnlockFlag.True : ArtworkUnlockFlag.False,
                isGradeMax ? ArtworkGradeMaxLimitFlag.True : ArtworkGradeMaxLimitFlag.False
            );
        }
    }
}
