using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public class OprGachaUpperDataTranslator
    {
        public static OprDrawCountThresholdModel Translate(OprGachaUpperData data)
        {
            return new OprDrawCountThresholdModel(
                new DrawCountThresholdGroupId(data.UpperGroup),
                data.UpperType,
                new GachaThresholdCount(data.Count)
            );
        }
    }
}
