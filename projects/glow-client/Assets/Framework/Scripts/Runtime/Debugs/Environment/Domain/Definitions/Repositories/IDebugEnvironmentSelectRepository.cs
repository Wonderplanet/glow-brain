using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.Repositories
{
    public interface IDebugEnvironmentSelectRepository
    {
        UniTask Load(CancellationToken cancellationToken);
        void Save(EnvironmentModel model);
        EnvironmentModel GetLast();
    }
}
