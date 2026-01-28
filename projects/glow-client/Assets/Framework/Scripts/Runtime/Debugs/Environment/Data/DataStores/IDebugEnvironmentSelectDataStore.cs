using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Modules.Environment;

namespace WPFramework.Debugs.Environment.Data.DataStores
{
    public interface IDebugEnvironmentSelectDataStore
    {
        UniTask Load(CancellationToken cancellationToken);
        void Save(EnvironmentData environmentData);
        EnvironmentData Get();
    }
}
