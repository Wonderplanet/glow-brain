using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MissionEventUpdateAndFetchResultDataTranslator
    {
        public static MissionEventUpdateAndFetchResultModel ToMissionEventUpdateAndFetchResultModel(
            MissionEventUpdateAndFetchResultData missionEventUpdateAndFetchResultData)
        {
            var missionEventModels = missionEventUpdateAndFetchResultData.MissionEvents
                .Select(mission => new MissionEventModel(
                    new MasterDataId(mission.MstEventId), 
                    mission.UsrMissionEvents.Select(UserMissionEventDataTranslator.ToUserMissionEventModel).ToList()
            )).ToList();

            return new MissionEventUpdateAndFetchResultModel(missionEventModels);
        }
    }
}