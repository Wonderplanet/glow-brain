using System;
using System.Collections;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Data.DataStores.Cache
{
    internal struct MstCacheData
    {
        public MasterType MasterType { get; }
        public Type Type { get; }
        public IEnumerable Data { get; }

        public MstCacheData(MasterType masterType, Type type, IEnumerable data)
        {
            MasterType = masterType;
            Type = type;
            Data = data;
        }
    }
}
