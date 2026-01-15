using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.Repositories
{
    public interface IDebugEnvironmentTargetRepository
    {
        UniTask Load(CancellationToken cancellationToken);
        DebugEnvironmentTargetModel Get();
    }
}
