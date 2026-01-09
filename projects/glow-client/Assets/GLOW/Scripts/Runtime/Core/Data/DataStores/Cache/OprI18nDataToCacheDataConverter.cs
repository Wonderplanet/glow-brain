using System.Collections.Generic;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Data.DataStores.Cache
{
    internal class OprI18nDataToCacheDataConverter : IMstDataToCacheDataConverter
    {
        readonly OprI18nData _data;

        public OprI18nDataToCacheDataConverter(OprI18nData data)
        {
            _data = data;
        }

        IEnumerable<MstCacheData> IMstDataToCacheDataConverter.Convert(Language language)
        {
            return new[]
            {
                new MstCacheData(MasterType.OprI18n, typeof(OprGachaI18nData), _data.OprGachaI18n),
                new MstCacheData(MasterType.OprI18n, typeof(OprGachaDisplayUnitI18nData), _data.OprGachaDisplayUnitI18n),
                new MstCacheData(MasterType.OprI18n, typeof(OprCampaignI18nData), _data.OprCampaignI18n),
                new MstCacheData(MasterType.OprI18n, typeof(OprProductI18nData), _data.OprProductI18n)
            };
        }
    }
}
