using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class MissionUpdateAndFetchResultDataTranslator
    {
        public static MissionUpdateAndFetchResultModel ToMissionUpdateAndFetchResultData(
            MissionUpdateAndFetchResultData missionUpdateAndFetchResultData)
        {
            var achievementMissionData = missionUpdateAndFetchResultData.UsrMissionAchievements;
            var achievementMissionModels = achievementMissionData?.Select(data => new UserMissionAchievementModel(
                new MasterDataId(data.MstMissionAchievementId),
                new MissionProgress(data.Progress),
                new MissionClearFrag(data.IsCleared),
                new MissionReceivedFlag(data.IsReceivedReward))).ToArray();
            
            var dailyBonusMissionData = missionUpdateAndFetchResultData.UsrMissionDailyBonuses;
            var dailyBonusMissionModels = dailyBonusMissionData?.Select(data => new UserMissionDailyBonusModel(
                new MasterDataId(data.MstMissionDailyBonusId),
                new MissionProgress(data.Progress),
                new MissionClearFrag(data.IsCleared),
                new MissionReceivedFlag(data.IsReceivedReward))).ToArray();
            
            var dailyMissionData = missionUpdateAndFetchResultData.UsrMissionDailies;
            var dailyMissionModels = dailyMissionData?.Select(data => new UserMissionDailyModel(
                new MasterDataId(data.MstMissionDailyId),
                new MissionProgress(data.Progress),
                new MissionClearFrag(data.IsCleared),
                new MissionReceivedFlag(data.IsReceivedReward))).ToArray();
                
            var weeklyMissionData = missionUpdateAndFetchResultData.UsrMissionWeeklies;
            var weeklyMissionModels = weeklyMissionData?.Select(data => new UserMissionWeeklyModel(
                new MasterDataId(data.MstMissionWeeklyId),
                new MissionProgress(data.Progress),
                new MissionClearFrag(data.IsCleared),
                new MissionReceivedFlag(data.IsReceivedReward))).ToArray();

            var beginnerMissionData = missionUpdateAndFetchResultData.UsrMissionBeginners;
            var beginnerMissionModels = beginnerMissionData?.Select(data => new UserMissionBeginnerModel(
                new MasterDataId(data.MstMissionBeginnerId),
                new MissionProgress(data.Progress),
                new MissionClearFrag(data.IsCleared),
                new MissionReceivedFlag(data.IsReceivedReward))).ToArray();
            
            var beginnerMissionDayNumber = new BeginnerMissionDaysFromStart(missionUpdateAndFetchResultData.MissionBeginnerDaysFromStart);
            
            var bonusPointData = missionUpdateAndFetchResultData.UsrMissionBonusPoints;
            var userMissionBonusPointModels = bonusPointData?
                .Select(data => new UserMissionBonusPointModel(
                    data.MissionType,
                    new BonusPoint(data.Point),
                    data.ReceivedRewardPoints.Select(point => new BonusPoint(point)).ToArray())).ToArray();
            
            return new MissionUpdateAndFetchResultModel(achievementMissionModels, dailyBonusMissionModels, dailyMissionModels, weeklyMissionModels, beginnerMissionModels, beginnerMissionDayNumber, userMissionBonusPointModels);
        }
    }
}