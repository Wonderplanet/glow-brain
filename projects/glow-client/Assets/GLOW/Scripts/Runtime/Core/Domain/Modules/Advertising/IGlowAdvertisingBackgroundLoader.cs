using System;
using System.Threading;
using GLOW.Core.Modules.Advertising.AppIdResolver;

namespace GLOW.Core.Modules.Advertising
{
    public interface IGlowAdvertisingBackgroundLoader : IDisposable
    {
        void LoadRewardAd(
            CancellationToken cancellationToken,
            GlowRewardAppId rewardAppId,
            IGlowAdvertisingBackgroundLoadEventHandler eventHandler);

        bool IsRequestingAd();
        string GetRequestingAd();
        void CancelAll();
    }
}