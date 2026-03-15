using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstHomeKomaPatternDataTranslator
    {
        public static MstHomeKomaPatternModel Translate(
            MstHomeKomaPatternData data,
            MstHomeKomaPatternI18nData i18n)
        {
            return new MstHomeKomaPatternModel(
                new MasterDataId(data.Id),
                new HomeMainKomaPatternAssetKey(data.AssetKey),
                new HomeMainKomaPatternName(i18n.Name));
        }
    }
}