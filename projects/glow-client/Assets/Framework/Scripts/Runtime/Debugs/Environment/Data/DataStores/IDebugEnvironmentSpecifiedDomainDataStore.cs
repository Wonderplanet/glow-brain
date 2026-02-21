using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Data.Data;

namespace WPFramework.Debugs.Environment.Data.DataStores
{
    public interface IDebugEnvironmentSpecifiedDomainDataStore
    {
        UniTask Load(CancellationToken cancellationToken);
        void Save(DebugEnvironmentSpecifiedDomainData debugEnvironmentSpecifiedDomainData);
        DebugEnvironmentSpecifiedDomainData Get();
        void Delete();
    }
}
