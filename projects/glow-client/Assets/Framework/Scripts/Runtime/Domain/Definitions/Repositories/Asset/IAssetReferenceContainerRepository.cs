using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using Object = UnityEngine.Object;

namespace WPFramework.Domain.Repositories
{
    public interface IAssetReferenceContainerRepository : IDisposable
    {
        UniTask Load<T>(CancellationToken cancellationToken, string containerKey, string assetKey) where T : Object;
        IAssetReferenceContainer<T> Get<T>(string containerKey) where T : class;
        void Unload(string containerKey, string assetKey);
    }
}
