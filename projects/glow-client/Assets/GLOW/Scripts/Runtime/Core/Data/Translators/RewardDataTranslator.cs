using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Data.Translators
{
    public static class RewardDataTranslator
    {
        public static RewardModel Translate(RewardData data)
        {
            if( data == null) return RewardModel.Empty;

            return new RewardModel(
                string.IsNullOrEmpty(data.ResourceId) ? MasterDataId.Empty : new MasterDataId(data.ResourceId),
                data.ResourceType,
                new PlayerResourceAmount(data.ResourceAmount),
                data.UnreceivedRewardReasonType,
                PreConversionResourceModelTranslator.ToPreConversionResourceModel(data.PreConversionResource));
        }
    }
}
