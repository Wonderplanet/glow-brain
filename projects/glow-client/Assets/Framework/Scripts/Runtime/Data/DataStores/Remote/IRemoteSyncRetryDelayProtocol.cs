using System;

namespace WPFramework.Data.DataStores
{
    public interface IRemoteSyncRetryDelayProtocol
    {
        TimeSpan CalculateDelayTime(int attempt);
    }
}
