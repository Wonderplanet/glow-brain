using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;

namespace GLOW.Core.Modules.Advertising
{
    public interface IAdvertisingPlayer
    {
        UniTask<GlowAdPlayRewardResultData> ShowAdAsync(
            IAARewardFeatureType iaaRewardFeatureType,
            CancellationToken cancellationToken);
    }
}
