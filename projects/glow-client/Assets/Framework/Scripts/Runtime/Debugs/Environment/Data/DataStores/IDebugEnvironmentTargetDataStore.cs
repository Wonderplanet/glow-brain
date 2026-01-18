using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Data.Data;

namespace WPFramework.Debugs.Environment.Data.DataStores
{
    public interface IDebugEnvironmentTargetDataStore
    {
        UniTask Load(CancellationToken cancellationToken);
        DebugEnvironmentTargetData Get();
    }
}
