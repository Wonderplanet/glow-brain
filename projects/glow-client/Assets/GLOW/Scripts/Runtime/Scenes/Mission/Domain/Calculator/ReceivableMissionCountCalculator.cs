using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;

namespace GLOW.Scenes.Mission.Domain.Calculator
{
    public static class ReceivableMissionCountCalculator
    {
        public static int GetReceivableMissionCount(MissionAchievementResultModel missionAchievementResultModel, MissionDailyBonusResultModel missionDailyBonusResultModel, MissionDailyResultModel missionDailyResultModel, MissionWeeklyResultModel missionWeeklyResultModel)
        {
            var receivableAchievementCount =
                missionAchievementResultModel.AchievementCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableDailyBonusCount =
                missionDailyBonusResultModel.MissionDailyBonusCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableDailyCount =
                missionDailyResultModel.MissionDailyModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableWeeklyCount =
                missionWeeklyResultModel.MissionWeeklyModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableDailyPointCount =
                missionDailyResultModel.BonusPointResultModel.BonusPointCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableWeeklyPointCount =
                missionWeeklyResultModel.BonusPointResultModel.BonusPointCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);

            var totalCount = receivableAchievementCount + receivableDailyBonusCount + receivableDailyCount +
                             receivableWeeklyCount  + receivableDailyPointCount +
                             receivableWeeklyPointCount;

            return totalCount;
        }

        public static int GetReceivableMissionBeginnerCount(
            MissionBeginnerResultModel missionBeginnerResultModel,
            BeginnerMissionDaysFromStart beginnerMissionDaysFromStart)
        {
            var receivableBeginnerCount = missionBeginnerResultModel.MissionBeginnerModel
                .Where(cell => cell.BeginnerMissionDayNumber <= beginnerMissionDaysFromStart)
                .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            var receivableBeginnerPointCount =
                missionBeginnerResultModel.BonusPointResultModel.BonusPointCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);

            return receivableBeginnerCount + receivableBeginnerPointCount;
        }

        public static int GetReceivableMissionEventCount(
            EventMissionAchievementResultModel eventMissionAchievementResultModel)
        {
            var receivableAchievementCount =
                eventMissionAchievementResultModel.OpeningEventAchievementCellModels
                    .Count(cell => cell.MissionStatus == MissionStatus.Receivable);

            return receivableAchievementCount;
        }
        
        public static Dictionary<MasterDataId, int> GetReceivableMissionEventCountDictionary(
            EventMissionAchievementResultModel eventMissionAchievementResultModel)
        {
            var receivableAchievementCountDictionary =
                eventMissionAchievementResultModel.OpeningEventAchievementCellModels
                    .GroupBy(cell => cell.EventId)
                    .ToDictionary(g => g.Key, g => g.Count(cell => cell.MissionStatus == MissionStatus.Receivable));

            return receivableAchievementCountDictionary;
        }

        public static int GetReceivableMissionOfAdventBattleCount(
            AdventBattleMissionFetchResultModel adventBattleMissionFetchResultModel)
        {
            var receivableMissionCount = adventBattleMissionFetchResultModel
                .AdventBattleMissionCellModels
                .Count(cell => cell.MissionStatus == MissionStatus.Receivable);
            return receivableMissionCount;
        }
    }
}
