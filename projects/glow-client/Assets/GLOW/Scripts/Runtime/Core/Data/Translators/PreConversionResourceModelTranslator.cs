using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class PreConversionResourceModelTranslator
    {
        public static PreConversionResourceModel ToPreConversionResourceModel(PreConversionResourceData data)
        {
            if(data == null) return PreConversionResourceModel.Empty;

            return new PreConversionResourceModel(
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount)
            );
        }
    }
}