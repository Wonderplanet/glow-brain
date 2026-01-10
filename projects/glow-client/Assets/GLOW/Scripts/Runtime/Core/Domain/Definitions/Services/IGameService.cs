using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Services
{
    public interface IGameService
    {
        UniTask<GameVersionModel> FetchVersion(CancellationToken cancellationToken);
        UniTask<GameUpdateAndFetchResultModel> UpdateAndFetch(CancellationToken cancellationToken);
        UniTask<GameFetchResultModel> Fetch(CancellationToken cancellationToken);
        UniTask<GameBadgeResultModel> Badge(CancellationToken cancellationToken);
        UniTask<GameServerTimeModel> FetchServerTime(CancellationToken cancellationToken);
    }
}
