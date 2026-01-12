using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Services
{
    public interface IEnvironmentService
    {
        UniTask FetchEnvironment(CancellationToken cancellationToken);
        EnvironmentModel FindConnectionEnvironment();
    }
}