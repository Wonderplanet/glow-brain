using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Polling
{
    public interface IPollingTask : IPollingListener
    {
        UniTask OnExecute(CancellationToken cancellationToken);
    }
}
