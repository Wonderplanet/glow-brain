using System;

namespace WPFramework.Modules.Polling
{
    public interface IPollingListener
    {
        void OnInitialize();
        void OnStarted();
        void OnFinished();
        void OnFailed(Exception e);
        void OnCanceled();
    }
}
