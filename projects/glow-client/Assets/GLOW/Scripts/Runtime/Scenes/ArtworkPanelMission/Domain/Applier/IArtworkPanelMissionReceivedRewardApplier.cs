using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Applier
{
    public interface IArtworkPanelMissionReceivedRewardApplier
    {
        void UpdateGameFetchModel(
            UserParameterModel userParameterModel,
            MasterDataId mstArtworkPanelMissionId,
            int receivableMissionEventCount);
        
        void UpdateGameFetchOtherModel(MissionBulkReceiveRewardResultModel resultModel);
    }
}