using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public class MissionBulkReceiveRewardResultDataTranslator
    {
         public static MissionBulkReceiveRewardResultModel ToMissionBulkReceiveRewardResultModel(
            MissionBulkReceiveRewardResultData missionBulkReceiveRewardResultData)
         {
             var receiveRewardData = missionBulkReceiveRewardResultData.MissionReceiveRewards;
             var receiveRewardMissionModels = receiveRewardData
                 .Select(data => new MissionReceiveRewardModel(
                     data.MissionType,
                     new MasterDataId(data.MstMissionId),
                     data.UnreceivedRewardReasonType ?? UnreceivedRewardReasonType.None))
                 .ToList();

            var rewardData = missionBulkReceiveRewardResultData.MissionRewards;
            var userMissionRewardModels = rewardData
                .Select(MissionRewardDataTranslator.ToMissionRewardModel)
                .ToList();

            var achievementMissionData = missionBulkReceiveRewardResultData.UsrMissionAchievements;
            var achievementMissionModels = achievementMissionData
                ?.Select(data => new UserMissionAchievementModel(
                    new MasterDataId(data.MstMissionAchievementId),
                    new MissionProgress(data.Progress),
                    new MissionClearFrag(data.IsCleared),
                    new MissionReceivedFlag(data.IsReceivedReward)))
                .ToList();

            var dailyMissionData = missionBulkReceiveRewardResultData.UsrMissionDailies;
            var dailyMissionModels = dailyMissionData
                ?.Select(data => new UserMissionDailyModel(
                    new MasterDataId(data.MstMissionDailyId),
                    new MissionProgress(data.Progress),
                    new MissionClearFrag(data.IsCleared),
                    new MissionReceivedFlag(data.IsReceivedReward)))
                .ToList();

            var weeklyMissionData = missionBulkReceiveRewardResultData.UsrMissionWeeklies;
            var weeklyMissionModels = weeklyMissionData
                ?.Select(data => new UserMissionWeeklyModel(
                    new MasterDataId(data.MstMissionWeeklyId),
                    new MissionProgress(data.Progress),
                    new MissionClearFrag(data.IsCleared),
                    new MissionReceivedFlag(data.IsReceivedReward)))
                .ToList();

            var beginnerMissionData = missionBulkReceiveRewardResultData.UsrMissionBeginners;
            var beginnerMissionModels = beginnerMissionData
                ?.Select(data => new UserMissionBeginnerModel(
                    new MasterDataId(data.MstMissionBeginnerId),
                    new MissionProgress(data.Progress),
                    new MissionClearFrag(data.IsCleared),
                    new MissionReceivedFlag(data.IsReceivedReward)))
                .ToList();

            var eventData = missionBulkReceiveRewardResultData.MissionEvents;
            var eventModels = eventData
                ?.Select(ToMissionEventModel)
                .ToList();

            var bonusPointData = missionBulkReceiveRewardResultData.UsrMissionBonusPoints;
            var userMissionBonusPointModels = bonusPointData
                .Select(data => new UserMissionBonusPointModel(data.MissionType,
                    new BonusPoint(data.Point),
                    data.ReceivedRewardPoints
                        .Select(point => new BonusPoint(point))
                        .ToList()))
                .ToList();

            var parameterData = missionBulkReceiveRewardResultData.UsrParameter;
            var userParameterModel = new UserParameterModel(
                new UserLevel(parameterData.Level),
                new UserExp(parameterData.Exp),
                new Coin(parameterData.Coin),
                new Stamina(parameterData.Stamina),
                parameterData.StaminaUpdatedAt,
                new FreeDiamond(parameterData.FreeDiamond),
                new PaidDiamondIos(parameterData.PaidDiamondIos),
                new PaidDiamondAndroid(parameterData.PaidDiamondAndroid),
                new UserDailyBuyStamina(parameterData.DailyBuyStaminaDiamondLimit, parameterData.DailyBuyStaminaAdLimit));

            var itemData = missionBulkReceiveRewardResultData.UsrItems;
            var userItemModels = itemData
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToArray();

            var unitData = missionBulkReceiveRewardResultData.UsrUnits;
            var userUnitModels = unitData
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToArray();

            var userLevelUpModel = UserLevelUpTranslator.ToUserLevelUpResultModel(missionBulkReceiveRewardResultData.UserLevel);

            var conditionPackModels = missionBulkReceiveRewardResultData.UsrConditionPacks
                .Select(UserConditionPackDataTranslator.ToModel)
                .ToList();

            return new MissionBulkReceiveRewardResultModel(
                receiveRewardMissionModels,
                userMissionRewardModels,
                achievementMissionModels,
                dailyMissionModels,
                weeklyMissionModels,
                beginnerMissionModels,
                eventModels,
                userMissionBonusPointModels,
                userParameterModel,
                userItemModels,
                userUnitModels,
                userLevelUpModel,
                conditionPackModels);
         }

         static MissionEventModel ToMissionEventModel(MissionEventData data)
         {
             var eventModels = data.UsrMissionEvents
                 .Select(userData => new UserMissionEventModel(
                     new MasterDataId(userData.MstMissionEventId),
                     new MissionProgress(userData.Progress),
                     new MissionClearFrag(userData.IsCleared),
                     new MissionReceivedFlag(userData.IsReceivedReward)))
                 .ToList();

             return new MissionEventModel(
                 new MasterDataId(data.MstEventId),
                 eventModels);
         }

    }
}
