using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.Modules
{
    public interface IDebugEnvironmentService
    {
        UniTask FetchEnvironment(CancellationToken cancellationToken);
        EnvironmentModel FindConnectionEnvironment();
        bool ChangeConnectionEnvironment(string environmentName);
        EnvironmentListModel FetchEnvironmentList();
    }
}
