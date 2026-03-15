using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UnitEncyclopediaRewardDataTranslator
    {
        public static RewardModel TranslateToPlayerResourceResult(UnitEncyclopediaRewardData data)
        {
            return RewardDataTranslator.Translate(data.Reward);
        }
    }
}
