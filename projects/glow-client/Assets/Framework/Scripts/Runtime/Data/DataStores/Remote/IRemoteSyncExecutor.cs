using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Data.DataStores
{
    public interface IRemoteSyncExecutor<T> where T : RemoteSyncData
    {
        UniTask Sync(CancellationToken cancellationToken, RemoteSyncTargetData<T>[] data);
    }
}
