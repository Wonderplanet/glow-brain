using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialGachaResultDataTranslator
    {
        public static GachaResultModel Translate(GachaResultData data)
        {
            return new GachaResultModel(
                RewardDataTranslator.Translate(data.Reward),
                data.PrizeType
            );
        }
    }
}
