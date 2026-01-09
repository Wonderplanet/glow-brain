using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Data.DataStores.Cache
{
    internal interface IMstDataToCacheDataConverter
    {
        public IEnumerable<MstCacheData> Convert(Language language);
    }
}
