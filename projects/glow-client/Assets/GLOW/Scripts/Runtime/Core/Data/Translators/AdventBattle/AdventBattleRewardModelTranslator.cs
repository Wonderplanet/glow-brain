using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleRewardModelTranslator
    {
        public static AdventBattleRewardModel ToAdventBattleRewardModel(AdventBattleRewardData data)
        {
            return new AdventBattleRewardModel(
                data.RewardCategory,
                RewardDataTranslator.Translate(data.Reward));
        }
    }
}
