using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Core.Data.Translators
{
    public class MissionAdventBattleFetchDataTranslator
    {
        public static MissionAdventBattleFetchResultModel ToMissionAdventBattleModel(
            MissionAdventBattleFetchResultData missionAdventBattleFetchResultData)
        {
            var userEventMissionModels = missionAdventBattleFetchResultData
                .UsrMissionEvents
                .Select(UserMissionEventDataTranslator.ToUserMissionEventModel)
                .ToList();
            
            var userLimitedTermMissionModels = missionAdventBattleFetchResultData
                .UsrMissionLimitedTerms
                .Select(UserMissionLimitedTermDataTranslator.ToUserMissionLimitedTermModel)
                .ToList();
                
            return new MissionAdventBattleFetchResultModel(
                userEventMissionModels,
                userLimitedTermMissionModels);
        }
    }
}