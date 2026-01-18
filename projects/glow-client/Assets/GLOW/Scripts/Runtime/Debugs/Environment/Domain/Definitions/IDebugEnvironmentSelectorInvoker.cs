#if GLOW_DEBUG
using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Debugs.Environment.Domain
{
    public interface IDebugEnvironmentSelectorInvoker
    {
        UniTask Invoke(CancellationToken cancellationToken);
    }
}
#endif // GLOW_DEBUG