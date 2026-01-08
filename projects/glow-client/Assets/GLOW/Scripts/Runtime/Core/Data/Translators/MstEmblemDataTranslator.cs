using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEmblemDataTranslator
    {
        public static MstEmblemModel Translate(MstEmblemData data, MstEmblemI18nData i18n)
        {
            return new MstEmblemModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstSeriesId),
                data.EmblemType,
                new EmblemAssetKey(data.AssetKey),
                new EmblemName(i18n.Name),
                new EmblemDescription(i18n.Description));
        }
    }
}
