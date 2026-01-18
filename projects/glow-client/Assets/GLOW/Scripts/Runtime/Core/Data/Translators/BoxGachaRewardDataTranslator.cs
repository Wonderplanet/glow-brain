using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.BoxGacha;

namespace GLOW.Core.Data.Translators
{
    public static class BoxGachaRewardDataTranslator
    {
        public static BoxGachaRewardModel ToBoxGachaRewardModel(BoxGachaRewardData data)
        {
            var rewardModel = RewardDataTranslator.Translate(data.Reward);
            return new BoxGachaRewardModel(rewardModel);
        }
    }
}