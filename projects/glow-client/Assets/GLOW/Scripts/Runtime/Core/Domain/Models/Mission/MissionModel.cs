using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionModel(
        List<UserMissionAchievementModel> UserMissionAchievementModels,
        List<UserMissionDailyBonusModel> UserMissionDailyBonusModels,
        List<UserMissionDailyModel> UserMissionDailyModels,
        List<UserMissionWeeklyModel> UserMissionWeeklyModels,
        List<UserMissionBeginnerModel> UserMissionBeginnerModels,
        BeginnerMissionDaysFromStart BeginnerMissionDaysFromStart,
        List<UserMissionBonusPointModel> UserMissionBonusPointModels)
    {
        public static MissionModel Empty { get; } = new(
            new List<UserMissionAchievementModel>(),
            new List<UserMissionDailyBonusModel>(),
            new List<UserMissionDailyModel>(),
            new List<UserMissionWeeklyModel>(),
            new List<UserMissionBeginnerModel>(),
            BeginnerMissionDaysFromStart.Empty,
            new List<UserMissionBonusPointModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        } 

        public static UserMissionAchievementModel AchievementEmpty(MasterDataId id)
        {
            return UserMissionAchievementModel.Empty with
            {
                MstMissionAchievementId = id
            };
        }

        public static UserMissionDailyBonusModel DailyBonusEmpty(MasterDataId id)
        {
            return UserMissionDailyBonusModel.Empty with
            {
                MstMissionDailyBonusId = id
            };
        }

        public static UserMissionDailyModel DailyEmpty(MasterDataId id)
        {
            return UserMissionDailyModel.Empty with
            {
                MstMissionDailyId = id
            };
        }

        public static UserMissionWeeklyModel WeeklyEmpty(MasterDataId id)
        {
            return UserMissionWeeklyModel.Empty with
            {
                MstMissionWeeklyId = id
            };
        }

        public static UserMissionBeginnerModel BeginnerEmpty(MasterDataId id)
        {
            return UserMissionBeginnerModel.Empty with
            {
                MstMissionBeginnerId = id
            };
        }

        public static UserMissionBonusPointModel BonusPointEmpty(MissionType missionType)
        {
            return UserMissionBonusPointModel.Empty with
            {
                MissionType = missionType
            };
        }
    };
}
