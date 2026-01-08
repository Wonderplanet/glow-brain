using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IGameApiContextHeaderModifier
    {
        UniTask Configure(ServerApi context, CancellationToken cancellationToken);
    }
}