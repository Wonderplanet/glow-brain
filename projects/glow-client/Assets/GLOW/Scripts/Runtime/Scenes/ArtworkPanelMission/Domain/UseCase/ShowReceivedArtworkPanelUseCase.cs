using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.UseCase
{
    public class ShowReceivedArtworkPanelUseCase
    {
        [Inject] IMissionOfArtworkPanelRepository MissionOfArtworkPanelRepository { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }

        public IReadOnlyList<ArtworkFragmentAcquisitionModel> GetAndClearReceivedArtworkPanelInfo()
        {
            var receivedUserArtworks = MissionOfArtworkPanelRepository. GetReceivedUserArtworkModels();
            var receivedUserArtworkFragments = MissionOfArtworkPanelRepository. GetAcquiredUserArtworkFragmentModels();
            var beforeUserArtworks = MissionOfArtworkPanelRepository. GetBeforeUserArtworkModels();
            var beforeUserArtworkFragments = MissionOfArtworkPanelRepository. GetBeforeUserArtworkFragmentModels();

            var acquiredPlayerResourceModels = ArtworkFragmentAcquisitionModelFactory
                .CreateArtworkFragmentAcquisitionModels(
                    receivedUserArtworks,
                    receivedUserArtworkFragments,
                    beforeUserArtworks,
                    beforeUserArtworkFragments);

            // 副作用: 受け取った情報をクリアする
            MissionOfArtworkPanelRepository.ClearReceivedArtworkPanelInfo();

            return acquiredPlayerResourceModels;
        }
    }
}
