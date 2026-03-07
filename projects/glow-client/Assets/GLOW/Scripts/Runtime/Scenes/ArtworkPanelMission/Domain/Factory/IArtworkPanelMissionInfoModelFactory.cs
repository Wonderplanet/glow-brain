using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Factory
{
    public interface IArtworkPanelMissionInfoModelFactory
    {
        ArtworkPanelMissionInfoModel CreateBySelectedMstEventId(
            MasterDataId selectedMstEventId,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels);
        
        ArtworkPanelMissionInfoModel CreateByLatestMstEventId(
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels);
    }
}