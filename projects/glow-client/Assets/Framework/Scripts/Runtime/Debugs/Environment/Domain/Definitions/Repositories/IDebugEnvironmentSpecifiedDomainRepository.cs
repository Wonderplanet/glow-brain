using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.Repositories
{
    public interface IDebugEnvironmentSpecifiedDomainRepository
    {
        UniTask Load(CancellationToken cancellationToken);
        void Save(DebugEnvironmentSpecifiedDomainModel debugEnvironmentSpecifiedDomainModel);
        DebugEnvironmentSpecifiedDomainModel Get();
        void Delete();
    }
}
