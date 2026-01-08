using System;
using System.Collections.Generic;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;

namespace WPFramework.Data.DataStores
{
    public sealed class AssetReferenceContainer<T> : IAssetReferenceContainer<T> where T : class
    {
        Dictionary<string, IAssetReference> _assetReferences = new ();

        bool IAssetReferenceContainer<T>.Add(string assetKey, IAssetReference<T> assetReference)
        {
            if (assetReference == null)
            {
                throw new ArgumentNullException(nameof(assetReference));
            }

            if (assetReference.GetValue() is not T)
            {
                throw new ArgumentException($"assetReference.GetValue() is not {typeof(T)}");
            }

            if (_assetReferences.TryGetValue(assetKey, out var oldAssetReference))
            {
                oldAssetReference.Release();
                _assetReferences.Remove(assetKey);
            }

            _assetReferences.Add(assetKey, assetReference);
            assetReference.Retain();

            ApplicationLog.Log(nameof(AssetReferenceContainer<T>), $"Add {assetKey}");

            return true;
        }

        T IAssetReferenceContainer<T>.Get(string assetKey)
        {
            if (_assetReferences.TryGetValue(assetKey, out var assetReference))
            {
                return assetReference.GetValue() as T;
            }
            ApplicationLog.LogWarning(nameof(AssetReferenceContainer<T>), $"assetReference is not found {assetKey}");
            return null;

        }

        void IAssetReferenceContainer.Unload(string assetKey)
        {
            if (!_assetReferences.TryGetValue(assetKey, out var assetReference))
            {
                ApplicationLog.LogWarning(nameof(AssetReferenceContainer<T>), $"assetReference is not found {assetKey}");
                return;
            }

            assetReference.Release();
            _assetReferences.Remove(assetKey);
            ApplicationLog.Log(nameof(AssetReferenceContainer<T>), $"Unload {assetKey}");
        }

        public void Unload()
        {
            foreach (var assetReference in _assetReferences.Values)
            {
                assetReference.Release();
            }

            _assetReferences.Clear();
            ApplicationLog.Log(nameof(AssetReferenceContainer<T>), $"Unload");
        }

        public void Dispose()
        {
            Unload();
        }
    }
}
