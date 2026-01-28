using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Message;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Core.Data.Translators
{
    public class MessageReceiveResultTranslator
    {
        public static MessageReceiveResultModel ToMessageReceiveResultModel(
            MessageReceiveResultData messageReceiveResultData)
        {
            var rewardData = messageReceiveResultData.MessageRewards;
            var userMessageRewardModels = rewardData
                .Select(data => RewardDataTranslator.Translate(data.Reward))
                .ToList();

            var parameterData = messageReceiveResultData.UsrParameter;
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(parameterData);
            
            var userUnitModels = messageReceiveResultData.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();
            
            var userItemModels = messageReceiveResultData.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();
            
            var userEmblemModels = messageReceiveResultData.UsrEmblems
                .Select(UserEmblemDataTranslator.ToUserEmblemModel)
                .ToList();

            var userLevelUpModel = UserLevelUpTranslator.ToUserLevelUpResultModel(messageReceiveResultData.UserLevel);

            var conditionPackModels = messageReceiveResultData.UsrConditionPacks
                .Select(UserConditionPackDataTranslator.ToModel)
                .ToList();

            return new MessageReceiveResultModel(
                userMessageRewardModels,
                messageReceiveResultData.IsEmblemDuplicated,
                userParameterModel,
                userUnitModels,
                userItemModels,
                userEmblemModels,
                userLevelUpModel,
                conditionPackModels
            );
        }
    }
}
