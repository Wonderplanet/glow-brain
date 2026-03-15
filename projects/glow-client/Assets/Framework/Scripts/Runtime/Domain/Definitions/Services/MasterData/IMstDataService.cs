using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Domain.Services
{
    public interface IMstDataService
    {
        UniTask<byte[]> FetchMstData(CancellationToken cancellationToken, string path);
    }
}
