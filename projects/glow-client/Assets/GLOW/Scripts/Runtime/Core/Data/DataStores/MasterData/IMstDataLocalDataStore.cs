using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Core.Data.DataStores
{
    public interface IMstDataLocalDataStore
    {
        UniTask Load(CancellationToken cancellationToken);
    }
}
