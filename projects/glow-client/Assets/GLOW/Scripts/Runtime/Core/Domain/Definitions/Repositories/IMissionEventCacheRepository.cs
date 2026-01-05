using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMissionEventCacheRepository
    {
        void SetMissionEventCacheModel(MissionEventCacheModel missionEventCacheModel);
        MissionEventModel GetMissionEventModelOrDefault(MasterDataId mstEventId);
        void UpdateMissionStatus(MasterDataId mstEventId, MissionType missionType, MasterDataId mstMissionId, MissionStatus missionStatus);
    }
}
