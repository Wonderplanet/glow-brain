using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Repositories
{
    public class MissionEventCacheRepository : IMissionEventCacheRepository
    {
        MissionEventCacheModel _missionEventCacheModel;

        public void SetMissionEventCacheModel(MissionEventCacheModel missionEventCacheModel)
        {
            _missionEventCacheModel = missionEventCacheModel;
        }

        public MissionEventModel GetMissionEventModelOrDefault(MasterDataId mstEventId)
        {
            return _missionEventCacheModel.MissionEventDictionary.TryGetValue(mstEventId, out var missionEventModel)
                ? missionEventModel
                : MissionEventModel.Empty;
        }

        public void UpdateMissionStatus(MasterDataId mstEventId, MissionType missionType, MasterDataId mstMissionId,
            MissionStatus missionStatus)
        {
            switch (missionType)
            {
                case MissionType.Event:
                    UpdateEventMissionStatus(mstEventId, mstMissionId, missionStatus);
                    break;
                default:
                    throw new ArgumentOutOfRangeException(nameof(missionType), missionType, null);
            }
        }

        void UpdateEventMissionStatus(MasterDataId mstEventId, MasterDataId mstMissionId, MissionStatus missionStatus)
        {
            _missionEventCacheModel.MissionEventDictionary.TryGetValue(mstEventId, out var missionEventModel);
            var eventMissionModels = missionEventModel?.UserMissionEventModels;
            if(eventMissionModels == null) return;

            var updateMissionIndex = eventMissionModels.FindIndex(mst =>
                    mst.MstMissionEventId == mstMissionId);
            if (updateMissionIndex == -1) return;

            var progress = eventMissionModels[updateMissionIndex].Progress;
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            eventMissionModels[updateMissionIndex] = new UserMissionEventModel(mstMissionId, progress, isClear, isReceivedReward);
        }
    }
}
