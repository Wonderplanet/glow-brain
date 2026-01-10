using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class DailyBonusRewardModelTranslator
    {
        public static DailyBonusRewardData ToDailyBonusRewardData(MissionReceivedDailyBonusModel model)
        {
            PreConversionResourceData preConversionData = model.RewardModel.PreConversionResource.IsEmpty() ? 
                null : 
                new PreConversionResourceData()
                {
                    ResourceId = model.RewardModel.PreConversionResource.ResourceId.IsEmpty() ? 
                        string.Empty : 
                        model.RewardModel.PreConversionResource.ResourceId.Value,
                    ResourceType = model.RewardModel.PreConversionResource.ResourceType,
                    ResourceAmount = model.RewardModel.PreConversionResource.ResourceAmount.Value
                };
            
            var rewardData = new RewardData()
            {
                UnreceivedRewardReasonType = model.RewardModel.UnreceivedRewardReasonType,
                ResourceId = model.RewardModel.ResourceId.IsEmpty() ? string.Empty : model.RewardModel.ResourceId.Value,
                ResourceType = model.RewardModel.ResourceType,
                ResourceAmount = model.RewardModel.Amount.Value,
                PreConversionResource = preConversionData
            };
            
            var dailyBonusRewardData = new DailyBonusRewardData()
            {
                MissionType = model.MissionType,
                LoginDayCount = model.LoginDayCount.Value,
                Reward = rewardData
            };
            
            return dailyBonusRewardData;
        }
    }
}