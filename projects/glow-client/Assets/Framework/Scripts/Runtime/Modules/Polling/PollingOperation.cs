using System;
using System.Threading;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Polling
{
    internal sealed class PollingOperation : IPollingOperable
    {
        PollingManager PollingManager { get; }
        public IPollingTask PollingTask { get; }
        public TimeSpan Interval { get; }
        public CancellationToken CancellationTokenByRegister { get; }
        public bool IsExecutionDelayed { get; }

        CancellationTokenSource _cancellationTokenSourceByStop;
        public CancellationToken CancellationTokenByStop => _cancellationTokenSourceByStop.Token;

        bool IsDisposed { get; set; } = false;

        public PollingOperation(
            PollingManager pollingManager,
            IPollingTask pollingTask,
            TimeSpan interval,
            CancellationToken cancellationToken,
            bool isExecutionDelayed
            )
        {
            PollingManager = pollingManager;
            PollingTask = pollingTask;
            Interval = interval;
            CancellationTokenByRegister = cancellationToken;
            IsExecutionDelayed = isExecutionDelayed;
        }

        bool IsStarting()
        {
            return _cancellationTokenSourceByStop != null;
        }

        public void Start()
        {
            if (IsDisposed)
            {
                ApplicationLog.Log(nameof(PollingOperation), $"Start: already disposed");
                return;
            }

            if (IsStarting())
            {
                ApplicationLog.Log(nameof(PollingOperation), $"Start: already start polling");
                return;
            }

            _cancellationTokenSourceByStop = new CancellationTokenSource();

            PollingManager.StartPolling(this);
        }

        public void Stop()
        {
            if (IsDisposed)
            {
                ApplicationLog.Log(nameof(PollingOperation), $"Stop: already disposed");
                return;
            }

            if (!IsStarting())
            {
                ApplicationLog.Log(nameof(PollingOperation), $"Stop: already stop polling");
                return;
            }

            Cancel();
        }

        public void Dispose()
        {
            if (IsDisposed)
            {
                ApplicationLog.Log(nameof(PollingOperation), $"Dispose: already disposed");
                return;
            }

            Cancel();
            PollingManager.Unregister(this);

            IsDisposed = true;
        }

        void Cancel()
        {
            if (_cancellationTokenSourceByStop == null)
            {
                return;
            }

            _cancellationTokenSourceByStop.Cancel();
            _cancellationTokenSourceByStop.Dispose();
            _cancellationTokenSourceByStop = null;
        }
    }
}
