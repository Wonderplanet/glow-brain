using System;
using WonderPlanet.InAppAdvertising;

namespace WPFramework.Domain.Modules
{
    public interface IAdvertisingBackgroundLoadEventHandler
    {
        void OnAdCompletedToLoad(AdHandlingToken adHandlingToken);
        void OnAdFailedToLoad(Exception exception, string adHandlingId);
        void OnAdCanceledToLoad(string adHandlingId);
    }
}
