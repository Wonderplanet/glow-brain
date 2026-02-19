using System;
using System.Collections.Generic;

namespace GLOW.Core.Modules.MultipleSwitchController
{
    public class MultipleSwitchHandler : IDisposable
    {
        readonly List<Action<MultipleSwitchHandler>> _disposedCallbackList = new ();

        public void Dispose()
        {
            _disposedCallbackList.ForEach(callback => callback(this));
            _disposedCallbackList.Clear();
        }

        public void AddDisposedCallback(Action<MultipleSwitchHandler> callback)
        {
            _disposedCallbackList.Add(callback);
        }

        public void RemoveDisposedCallback(Action<MultipleSwitchHandler> callback)
        {
            _disposedCallbackList.Remove(callback);
        }
    }
}
