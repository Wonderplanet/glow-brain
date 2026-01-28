using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.ResourceManagement;
using WPFramework.Data.DataStores;
using WPFramework.Domain.Repositories;
using Zenject;

namespace WPFramework.Data.Repositories
{
    public sealed class AssetReferenceContainerRepository : IAssetReferenceContainerRepository
    {
        [Inject] IAssetSource AssetSource { get; }
        [Inject] IAssetReferenceContainerDataStore AssetReferenceContainerDataStore { get; }

        async UniTask IAssetReferenceContainerRepository.Load<T>(CancellationToken cancellationToken, string containerKey, string assetKey)
        {
            var assetReference = await AssetSource.GetAsset<T>(cancellationToken, assetKey);
            AssetReferenceContainerDataStore.Add(containerKey, assetKey, assetReference);
        }

        IAssetReferenceContainer<T> IAssetReferenceContainerRepository.Get<T>(string containerKey) where T : class
        {
            return AssetReferenceContainerDataStore.Get<T>(containerKey);
        }

        void IAssetReferenceContainerRepository.Unload(string containerKey, string assetKey)
        {
            AssetReferenceContainerDataStore.Unload(containerKey, assetKey);
        }

        public void Dispose()
        {
            AssetReferenceContainerDataStore.Dispose();
        }
    }
}
