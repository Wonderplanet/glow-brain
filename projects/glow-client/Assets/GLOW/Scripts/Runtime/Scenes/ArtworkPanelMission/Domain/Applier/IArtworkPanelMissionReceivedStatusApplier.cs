using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Applier
{
    public interface IArtworkPanelMissionReceivedStatusApplier
    {
        IReadOnlyList<UserMissionLimitedTermModel> UpdateReceivedArtworkPanelMissions(
            IReadOnlyList<MissionReceiveRewardModel> receivedMissionModels);
    }
}