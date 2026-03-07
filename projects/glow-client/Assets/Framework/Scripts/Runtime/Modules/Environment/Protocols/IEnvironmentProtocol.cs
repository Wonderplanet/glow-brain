using System;
using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Environment
{
    public interface IEnvironmentProtocol : IDisposable
    {
        int Priority { get; }
        UniTask<EnvironmentListData> FetchEnvironmentList(CancellationToken cancellationToken);
    }
}
