using System.Collections.Generic;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Data.DataStores.Cache
{
    internal sealed class OprDataToCacheDataConverter : IMstDataToCacheDataConverter
    {
        readonly OprData _data;

        public OprDataToCacheDataConverter(OprData data)
        {
            _data = data;
        }

        IEnumerable<MstCacheData> IMstDataToCacheDataConverter.Convert(Language language)
        {
            return new[]
            {
                new MstCacheData(MasterType.Opr, typeof(OprProductData), _data.OprProduct),
                new MstCacheData(MasterType.Opr, typeof(OprGachaData), _data.OprGacha),
                new MstCacheData(MasterType.Opr, typeof(OprGachaUseResourceData), _data.OprGachaUseResource),
                new MstCacheData(MasterType.Opr, typeof(OprGachaUpperData), _data.OprGachaUpper),
                new MstCacheData(MasterType.Opr, typeof(OprCampaignData), _data.OprCampaign)
            };
        }
    }
}
