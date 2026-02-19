using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Scenes.AdventBattleMission.Domain.Applier
{
    public interface IAdventBattleMissionReceivedRewardApplier
    {
        
        void UpdateGameFetchModel(UserParameterModel userParameterModel,
            int receivableMissionEventCount);

        void UpdateGameFetchOtherModel(MissionReceiveRewardResultModel missionReceiveRewardResult);
        
        void UpdateGameFetchOtherModel(MissionBulkReceiveRewardResultModel missionBulkReceiveRewardResultModel);
    }
}