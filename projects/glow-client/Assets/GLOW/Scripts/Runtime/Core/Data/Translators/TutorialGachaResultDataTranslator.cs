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
                data.Reward.ResourceType,
                new MasterDataId(data.Reward.ResourceId),
                new ObscuredPlayerResourceAmount(data.Reward.ResourceAmount),
                PreConversionResourceModelTranslator.ToPreConversionResourceModel(data.Reward.PreConversionResource)
            );
        }
    }
}
