using System;
using WonderPlanet.ResourceManagement;

namespace WPFramework.Domain.Repositories
{
    public interface IAssetReferenceContainer : IDisposable
    {
        void Unload();
        void Unload(string assetKey);
    }

    public interface IAssetReferenceContainer<T> : IAssetReferenceContainer where T : class
    {
        bool Add(string assetKey, IAssetReference<T> assetReference);
        T Get(string assetKey);
    }
}
