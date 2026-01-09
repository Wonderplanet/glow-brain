using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class AdventBattleClearRewardModelTranslator
    {
        public static AdventBattleClearRewardModel ToAdventBattleClearRewardModel(AdventBattleClearRewardData data)
        {
            return new AdventBattleClearRewardModel(
                data.RewardCategory,
                data.Reward.UnreceivedRewardReasonType,
                data.Reward.ResourceType,
                new MasterDataId(data.Reward.ResourceId),
                new PlayerResourceAmount(data.Reward.ResourceAmount),
                PreConversionResourceModelTranslator.ToPreConversionResourceModel(data.Reward.PreConversionResource));
        }
    }
}
