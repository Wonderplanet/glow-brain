using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Factory
{
    public interface IArtworkPanelMissionResultModelFactory
    {
        ArtworkPanelMissionFetchResultModel CreateArtworkPanelMissionResultModel(
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            MasterDataId mstArtworkPanelMissionId);
    }
}