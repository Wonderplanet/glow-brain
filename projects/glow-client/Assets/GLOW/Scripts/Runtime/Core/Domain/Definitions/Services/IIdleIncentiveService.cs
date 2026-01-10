using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.IdleIncentive;

namespace GLOW.Core.Domain.Services
{
    public interface IIdleIncentiveService
    {
        UniTask<IdleIncentiveReceiveResultModel> Receive(CancellationToken cancellationToken);
        UniTask<IdleIncentiveReceiveResultModel> QuickReceiveByItem(CancellationToken cancellationToken);
        UniTask<IdleIncentiveReceiveResultModel> QuickReceiveByAd(CancellationToken cancellationToken);
    }
}
