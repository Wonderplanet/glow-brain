using System.Collections.Generic;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Repositories;
using WPFramework.Exceptions;
using WPFramework.Modules.Log;

namespace WPFramework.Data.DataStores
{
    public sealed class AssetReferenceContainerDataStore : IAssetReferenceContainerDataStore
    {
        Dictionary<string, IAssetReferenceContainer> _assetReferenceContainers = new();

        void IAssetReferenceContainerDataStore.Add<T>(string containerKey, string assetKey, IAssetReference<T> assetReference) where T : class
        {
            IAssetReferenceContainer<T> assetReferenceContainer = default;
            if (!_assetReferenceContainers.ContainsKey(containerKey))
            {
                assetReferenceContainer = new AssetReferenceContainer<T>();
                _assetReferenceContainers.Add(containerKey, assetReferenceContainer);
            }
            else
            {
                assetReferenceContainer = _assetReferenceContainers[containerKey] as IAssetReferenceContainer<T>;
            }

            if (assetReferenceContainer == null)
            {
                throw new AssetReferenceContainerException($"assetReferenceContainer is not {typeof(IAssetReferenceContainer<T>)}");
            }

            assetReferenceContainer.Add(assetKey, assetReference);

            ApplicationLog.Log(nameof(AssetReferenceContainerDataStore), $"Add {containerKey} {assetKey}");
        }

        IAssetReferenceContainer<T> IAssetReferenceContainerDataStore.Get<T>(string containerKey) where T : class
        {
            if (!_assetReferenceContainers.TryGetValue(containerKey, out var assetReferenceContainer))
            {
                return null;
            }

            return assetReferenceContainer as IAssetReferenceContainer<T> ??
                   throw new AssetReferenceContainerException($"assetReferenceContainer is not {typeof(IAssetReferenceContainer<T>)}");
        }

        void IAssetReferenceContainerDataStore.Unload(string containerKey, string assetKey)
        {
            if (!_assetReferenceContainers.TryGetValue(containerKey, out var assetReferenceContainer))
            {
                return;
            }

            assetReferenceContainer.Unload(assetKey);

            ApplicationLog.Log(nameof(AssetReferenceContainerDataStore), $"Unload {containerKey} {assetKey}");
        }

        public void Dispose()
        {
            foreach (var assetReferenceContainer in _assetReferenceContainers.Values)
            {
                assetReferenceContainer.Dispose();
            }

            _assetReferenceContainers.Clear();

            ApplicationLog.Log(nameof(AssetReferenceContainerDataStore), $"IAssetReferenceContainerDataStore.Dispose");
        }
    }
}
