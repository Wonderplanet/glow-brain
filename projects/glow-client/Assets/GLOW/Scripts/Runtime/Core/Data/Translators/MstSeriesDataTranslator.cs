using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstSeriesDataTranslator
    {
        public static MstSeriesModel ToModel(MstSeriesData data, MstSeriesI18nData i18nData)
        {
            return new MstSeriesModel(
                new MasterDataId(data.Id),
                new SeriesName(i18nData.Name),
                new SeriesAssetKey(data.AssetKey),
                new SeriesBannerAssetKey(data.BannerAssetKey),
                new SeriesPrefixWord(i18nData.PrefixWord),
                new JumpPlusUrl(data.JumpPlusUrl)
                );
        }

    }
}
