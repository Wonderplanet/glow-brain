using System;
using System.Collections.Generic;

namespace GLOW.Core.Data.Repositories
{
    public interface IMstModelCache : IDisposable
    {
        IEnumerable<T> GetOrCreate<T>(Func<IEnumerable<T>> createInstance);
        IEnumerable<T> GetOrCreate<T>(string key, Func<IEnumerable<T>> createInstance);
        void Clear();
        bool Remove<T>(string key);
        bool Remove<T>();
    }
}
