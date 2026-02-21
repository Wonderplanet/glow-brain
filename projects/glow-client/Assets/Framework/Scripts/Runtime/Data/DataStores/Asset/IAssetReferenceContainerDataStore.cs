using System;
using WonderPlanet.ResourceManagement;
using WPFramework.Domain.Repositories;

namespace WPFramework.Data.DataStores
{
    public interface IAssetReferenceContainerDataStore : IDisposable
    {
        void Add<T>(string containerKey, string assetKey, IAssetReference<T> assetReference) where T : class;
        IAssetReferenceContainer<T> Get<T>(string containerKey) where T : class;
        void Unload(string containerKey, string assetKey);
    }
}
