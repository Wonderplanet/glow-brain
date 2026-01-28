using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Domain.Model;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMissionCacheRepository
    {
        void SetMissionModel(MissionModel missionModel);
        MissionModel GetMissionModel();
        void SetReceivedBonusPointMissionRewards(ReceivedBonusPointMissionRewardResultModel receivedBonusPointMissionRewardResultModel);
        ReceivedBonusPointMissionRewardResultModel GetReceivedBonusPointMissionRewards();
        void ClearBonusPointMissionRewards();
        void UpdateMissionStatus(MissionType missionType, MasterDataId missionId, MissionStatus missionStatus);
        void UpdateBonusPointMissionProgress(MissionType missionType, BonusPoint progress);
        UserMissionBonusPointModel GetBonusPointMission(MissionType missionType);
        void UpdateBonusPointMission(MissionType missionType, IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels);
    }
}