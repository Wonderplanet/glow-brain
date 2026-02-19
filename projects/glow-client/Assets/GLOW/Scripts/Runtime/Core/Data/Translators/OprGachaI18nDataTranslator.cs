using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public class OprGachaI18nDataTranslator
    {
        public static OprGachaDisplayUnitI18nModel Translate(OprGachaDisplayUnitI18nData data)
        {
            return new OprGachaDisplayUnitI18nModel(
                new MasterDataId(data.OprGachaId),
                new MasterDataId(data.MstUnitId),
                new GachaDisplayUnitDescription(data.Description));
        }
    }
}
