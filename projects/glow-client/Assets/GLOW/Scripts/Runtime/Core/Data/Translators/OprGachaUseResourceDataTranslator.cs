using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public class OprGachaUseResourceDataTranslator
    {
        public static OprGachaUseResourceModel Translate(OprGachaUseResourceData data)
        {
            return new OprGachaUseResourceModel(
                new MasterDataId(data.OprGachaId),
                data.CostType,
                new MasterDataId(data.CostId),
                new CostAmount(data.CostNum),
                new GachaDrawCount(data.DrawCount),
                new GachaCostPriority(data.CostPriority)
            );
        }
    }
}
