using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.ExchangeShop;

namespace GLOW.Core.Data.Translators
{
    public class ExchangeRewardDataTranslator
    {
        public static ExchangeRewardModel Translate(ExchangeRewardData data)
        {
            return new ExchangeRewardModel(
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}
