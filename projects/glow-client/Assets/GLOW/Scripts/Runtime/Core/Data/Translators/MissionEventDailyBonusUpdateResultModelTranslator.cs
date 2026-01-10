using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class MissionEventDailyBonusUpdateResultModelTranslator
    {
        public static MissionEventDailyBonusUpdateResultModel  ToMissionEventDailyBonusUpdateResultModel(
            MissionEventDailyBonusUpdateResultData missionEventDailyBonusUpdateResultData)
        {
            var rewardData = missionEventDailyBonusUpdateResultData.EventDailyBonusRewards;
            var userMissionRewardModels = rewardData.Select(ToMissionEventDailyBonusRewardModel).ToList();

            var eventDailyBonusProgressModels = missionEventDailyBonusUpdateResultData.UsrMissionEventDailyBonusProgresses?
                .Select(progressData => new UserMissionEventDailyBonusProgressModel(
                    new MasterDataId(progressData.MstMissionEventDailyBonusScheduleId), 
                    new LoginDayCount(progressData.Progress)))
                .ToList() ?? new List<UserMissionEventDailyBonusProgressModel>(); 
            
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(missionEventDailyBonusUpdateResultData.UsrParameter);

            var itemData = missionEventDailyBonusUpdateResultData.UsrItems;
            var userItemModels = itemData
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToArray();

            var unitData = missionEventDailyBonusUpdateResultData.UsrUnits;
            var userUnitModels = unitData
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToArray();
            
            var userEmblemModels = missionEventDailyBonusUpdateResultData.UsrEmblems?
                .Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList() ?? new List<UserEmblemModel>();

            var userLevelUpModel = UserLevelUpTranslator.ToUserLevelUpResultModel(
                missionEventDailyBonusUpdateResultData.UserLevel);

            var conditionPackModels = missionEventDailyBonusUpdateResultData.UsrConditionPacks
                .Select(UserConditionPackDataTranslator.ToModel)
                .ToList();

            return new MissionEventDailyBonusUpdateResultModel(
                userMissionRewardModels, 
                eventDailyBonusProgressModels,
                userParameterModel,
                userUnitModels, 
                userItemModels, 
                userEmblemModels,
                userLevelUpModel, 
                conditionPackModels);
        }

        public static MissionEventDailyBonusRewardModel ToMissionEventDailyBonusRewardModel(EventDailyBonusRewardData data)
        {
            return new MissionEventDailyBonusRewardModel(
                new MasterDataId(data.MstMissionEventDailyBonusScheduleId),
                new LoginDayCount(data.LoginDayCount),
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}
