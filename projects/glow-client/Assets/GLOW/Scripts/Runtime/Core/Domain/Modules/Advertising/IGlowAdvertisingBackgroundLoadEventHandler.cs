using System;

namespace GLOW.Core.Modules.Advertising
{
    public interface IGlowAdvertisingBackgroundLoadEventHandler
    {
        void OnAdCompletedToLoad();
        void OnAdFailedToLoad(Exception exception);
        void OnAdCanceledToLoad();
    }
}