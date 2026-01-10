using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class MissionClearOnCallResultDataTranslator
    {
        public static MissionClearOnCallResultModel ToMissionClearOnCallResultModel(
            MissionClearOnCallResultData data)
        {
            var userMissionAchievements = data.UsrMissionAchievements
                ?.Select(user => new UserMissionAchievementModel(
                    new MasterDataId(user.MstMissionAchievementId),
                    new MissionProgress(user.Progress),
                    new MissionClearFrag(user.IsCleared),
                    new MissionReceivedFlag(user.IsReceivedReward)))
                .ToList() ?? new List<UserMissionAchievementModel>();
            
            var userMissionBeginners = data.UsrMissionBeginners
                ?.Select(user => new UserMissionBeginnerModel(
                    new MasterDataId(user.MstMissionBeginnerId), 
                    new MissionProgress(user.Progress), 
                    new MissionClearFrag(user.IsCleared), 
                    new MissionReceivedFlag(user.IsReceivedReward)))
                .ToList() ?? new List<UserMissionBeginnerModel>();
            
            return new MissionClearOnCallResultModel(
                userMissionAchievements,
                userMissionBeginners);
        }
    }
}