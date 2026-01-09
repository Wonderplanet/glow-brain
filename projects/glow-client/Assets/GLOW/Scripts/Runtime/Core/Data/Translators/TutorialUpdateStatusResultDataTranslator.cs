using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class TutorialUpdateStatusResultDataTranslator
    {
        public static TutorialUpdateStatusResultModel Translate(TutorialUpdateStatusResultData data)
        {
            var dailyBonus = data.DailyBonusRewards?
                .Select(bonus => new MissionReceivedDailyBonusModel(
                    bonus.MissionType,
                    new LoginDayCount(bonus.LoginDayCount),
                    RewardDataTranslator.Translate(bonus.Reward)))
                .ToList();

            var eventDailyBonusRewardModels = data.EventDailyBonusRewards?
                .Select(MissionEventDailyBonusUpdateResultModelTranslator.ToMissionEventDailyBonusRewardModel) 
                .ToList() ?? new List<MissionEventDailyBonusRewardModel>();
            
            var eventDailyBonusProgressModels = data.UsrMissionEventDailyBonusProgresses?
                .Select(progressData => new UserMissionEventDailyBonusProgressModel(
                    new MasterDataId(progressData.MstMissionEventDailyBonusScheduleId), 
                    new LoginDayCount(progressData.Progress)))
                .ToList() ?? new List<UserMissionEventDailyBonusProgressModel>();

            var userParameterModel = UserParameterTranslator.ToUserParameterModel(data.UsrParameter);
            
            var userUnitModel = data.UsrUnits?
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList() ?? new List<UserUnitModel>();
            
            var userItemModels = data.UsrItems?
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList() ?? new List<UserItemModel>();
            
            var userEmblemModels = data.UsrEmblems?
                .Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList() ?? new List<UserEmblemModel>();
            
            var userLevelUpResultModel = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            
            var userConditionPackModels = data.UsrConditionPacks?
                .Select(UserConditionPackDataTranslator.ToModel)
                .ToList() ?? new List<UserConditionPackModel>();
            
            return new TutorialUpdateStatusResultModel(
                data.UsrGachas.Select(UserGachaDataTranslator.ToUserGachaModel).ToList(),
                data.UsrIdleIncentive != null
                    ? UserIdleIncentiveDataTranslator.ToModel(data.UsrIdleIncentive)
                    : UserIdleIncentiveModel.Empty,
                dailyBonus,
                eventDailyBonusRewardModels,
                eventDailyBonusProgressModels,
                userParameterModel,
                userUnitModel,
                userItemModels,
                userEmblemModels,
                userLevelUpResultModel,
                userConditionPackModels
            );
        }
    }
}