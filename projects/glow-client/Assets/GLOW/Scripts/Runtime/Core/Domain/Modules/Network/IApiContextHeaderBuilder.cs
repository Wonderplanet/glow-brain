using Cysharp.Threading.Tasks;
using System.Threading;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IApiContextHeaderBuilder
    {
        UniTask Build(CancellationToken cancellationToken);
    }
}