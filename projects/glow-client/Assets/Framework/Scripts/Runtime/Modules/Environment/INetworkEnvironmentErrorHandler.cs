using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Environment
{
    public interface INetworkEnvironmentErrorHandler
    {
        UniTask<bool> HandleNetworkError(CancellationToken cancellationToken);
    }
}