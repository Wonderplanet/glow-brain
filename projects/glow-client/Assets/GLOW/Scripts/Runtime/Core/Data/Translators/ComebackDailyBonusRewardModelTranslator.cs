using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.ComebackDailyBonus;

namespace GLOW.Core.Data.Translators
{
    public static class ComebackDailyBonusRewardModelTranslator
    {
        public static ComebackBonusRewardData ToComebackDailyBonusRewardData(ComebackBonusRewardModel model)
        {
            PreConversionResourceData preConversionData = model.Reward.PreConversionResource.IsEmpty() ? 
                null : 
                new PreConversionResourceData()
                {
                    ResourceId = model.Reward.PreConversionResource.ResourceId.IsEmpty() ? 
                        string.Empty : 
                        model.Reward.PreConversionResource.ResourceId.Value,
                    ResourceType = model.Reward.PreConversionResource.ResourceType,
                    ResourceAmount = model.Reward.PreConversionResource.ResourceAmount.Value
                };
            
            var rewardData = new RewardData()
            {
                UnreceivedRewardReasonType = model.Reward.UnreceivedRewardReasonType,
                ResourceId = model.Reward.ResourceId.IsEmpty() ? string.Empty : model.Reward.ResourceId.Value,
                ResourceType = model.Reward.ResourceType,
                ResourceAmount = model.Reward.Amount.Value,
                PreConversionResource = preConversionData
            };
            
            var dailyBonusRewardData = new ComebackBonusRewardData()
            {
                MstComebackBonusScheduleId = model.MstComebackBonusScheduleId.Value,
                LoginDayCount = model.LoginDayCount.Value,
                Reward = rewardData
            };
            
            return dailyBonusRewardData;
        }
    }
}