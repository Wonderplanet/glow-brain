using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Data.Repositories
{
    public sealed class MstModelInMemoryCache : IMstModelCache
    {
        readonly Dictionary<string, IEnumerable> _cache = new Dictionary<string, IEnumerable>();

        bool _isDisposed;

        public IEnumerable<T> GetOrCreate<T>(Func<IEnumerable<T>> createInstance)
        {
            return GetOrCreate(string.Empty, createInstance);
        }

        public IEnumerable<T> GetOrCreate<T>(string key, Func<IEnumerable<T>> createInstance)
        {
            // NOTE: キャッシュキーがない場合は型のフルネームをキャッシュキーとする
            var cacheKey = GenerateCacheKey<T>(key);
            // NOTE: キャッシュがヒットした場合はキャッシュを利用する
            if (_cache.TryGetValue(cacheKey, out var value))
            {
                return (IEnumerable<T>)value;
            }

            var newValue = createInstance();
            var orCreate = newValue as T[] ?? newValue.ToArray();
            _cache.Add(cacheKey, orCreate);
            return orCreate;
        }

        public void Clear()
        {
            _cache.Clear();
        }

        public bool Remove<T>(string key)
        {
            // NOTE: keyが空の場合は、Tに関連するキャッシュを全て削除する
            if (string.IsNullOrEmpty(key))
            {
                var typePrefix = CacheKeyPrefix<T>();
                var keysToRemove = _cache.Keys.Where(k => k.StartsWith(typePrefix)).ToList();
                foreach (var k in keysToRemove)
                {
                    _cache.Remove(k);
                }

                return keysToRemove.Count > 0;
            }

            var cacheKey = GenerateCacheKey<T>(key);
            return _cache.Remove(cacheKey);
        }

        public bool Remove<T>()
        {
            return Remove<T>(string.Empty);
        }

        string GenerateCacheKey<T>(string key)
        {
            return $"{CacheKeyPrefix<T>()}_{key}";
        }

        string CacheKeyPrefix<T>()
        {
            return $"{typeof(T).FullName}_";
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            _isDisposed = true;

            Clear();
        }
    }
}
