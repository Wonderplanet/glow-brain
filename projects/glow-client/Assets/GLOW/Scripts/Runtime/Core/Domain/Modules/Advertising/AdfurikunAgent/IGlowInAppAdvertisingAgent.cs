using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using WonderPlanet.InAppAdvertising;

namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public interface IGlowInAppAdvertisingLoader

    {
        bool IsLoadedAd();
        UniTask LoadRewardedAd(CancellationToken cancellationToken, GlowRewardAppId rewardAppId);
    }
    public interface IGlowInAppAdvertisingAgent : IGlowInAppAdvertisingLoader, IDisposable
    {
        public bool IsInitialized { get; }
        public bool IsDisposed { get; }
        void Initialize(GlowRewardAppId rewardAppId);
        UniTask<GlowAdPlayRewardResultData> ShowAdAsync(IAARewardFeatureType type, CancellationToken cancellationToken);
    }
}
