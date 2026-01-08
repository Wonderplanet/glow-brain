using System.Collections.Generic;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model.AchievementMission;
using GLOW.Scenes.Mission.Domain.Model.DailyBonusMission;
using GLOW.Scenes.Mission.Domain.Model.DailyMission;
using GLOW.Scenes.Mission.Domain.Model.WeeklyMission;

namespace GLOW.Scenes.Mission.Domain.Creator
{
    public interface IMissionResultModelFactory
    {
        public MissionAchievementResultModel CreateMissionAchievementResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionAchievementModel> userMissionAchievementModels);

        public MissionDailyBonusResultModel CreateMissionDailyBonusResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionDailyBonusModel> userMissionDailyBonusModels,
            UserLoginInfoModel userLoginInfoModel,
            IReadOnlyList<MissionReceivedDailyBonusModel> missionReceivedDailyBonusModel);

        public MissionDailyResultModel CreateMissionDailyResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionDailyModel> userMissionDailyModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels);

        public MissionWeeklyResultModel CreateMissionWeeklyResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionWeeklyModel> userMissionWeeklyModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels);

        public MissionBeginnerResultModel CreateMissionBeginnerResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionBeginnerModel> userMissionBeginnerModels,
            IReadOnlyList<UserMissionBonusPointModel> userMissionBonusPointModels);

        public EventMissionAchievementResultModel CreateEventMissionAchievementResultModel(
            IReadOnlyList<MstEventModel> mstEventModels,
            ITimeProvider timeProvider,
            IMstMissionEventDataRepository missionDataRepository,
            IMstMissionRewardDataRepository mstRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionEventModel> userMissionEventAchievementModels);

        public EventMissionDailyBonusResultModel CreateEventMissionDailyBonusResultModel(
            MstEventModel mstEventModel,
            IMstMissionEventDataRepository missionDataRepository,
            IMstMissionRewardDataRepository mstRewardDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            MasterDataId mstEventScheduleId,
            IReadOnlyList<MissionEventDailyBonusRewardModel> eventDailyBonusRewardModels,
            IReadOnlyList<UserMissionEventDailyBonusProgressModel> userMissionEventDailyBonusProgressModels);

        public AdventBattleMissionFetchResultModel CreateAdventBattleMissionResultModel(
            IMstMissionDataRepository missionDataRepository,
            IPlayerResourceModelFactory playerResourceModelFactory,
            IReadOnlyList<UserMissionEventModel> userMissionEventModels,
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            ITimeProvider timeProvider,
            AdventBattleEndDateTime endDateTime);
    }
}
