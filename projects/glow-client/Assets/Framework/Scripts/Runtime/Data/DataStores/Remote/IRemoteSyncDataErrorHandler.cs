using System;

namespace WPFramework.Data.DataStores
{
    public interface IRemoteSyncDataErrorHandler
    {
        bool OnRemoteSyncFailed(Type dataType, Exception exception);
    }
}
