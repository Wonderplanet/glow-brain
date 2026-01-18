using System;

namespace WPFramework.Modules.Polling
{
    public interface IPollingOperable : IDisposable
    {
        void Start();
        void Stop();
    }
}
