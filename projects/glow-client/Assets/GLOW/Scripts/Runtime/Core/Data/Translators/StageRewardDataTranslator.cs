using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class StageRewardDataTranslator
    {
        public static StageRewardResultModel ToStageRewardResultModel(StageRewardData data)
        {
            var campaignPercentage = data.CampaignPercentage.HasValue ?
                new CampaignPercentage(data.CampaignPercentage.Value) :
                CampaignPercentage.Empty;

            return new StageRewardResultModel(
                data.RewardCategory,
                RewardDataTranslator.Translate(data.Reward),
                campaignPercentage,
                new StaminaLapNumber(data.LapNumber));
        }
    }
}
