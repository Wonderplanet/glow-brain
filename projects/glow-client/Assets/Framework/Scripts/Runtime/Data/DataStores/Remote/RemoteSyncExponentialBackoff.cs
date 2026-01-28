using System;

namespace WPFramework.Data.DataStores
{
    public sealed class RemoteSyncExponentialBackoff : IRemoteSyncRetryDelayProtocol
    {
        readonly TimeSpan _initialDelay;
        readonly double _factor;
        readonly int _maxVariableAttempts;

        public RemoteSyncExponentialBackoff(TimeSpan initialDelay, double factor = 2.0, int maxVariableAttempts = 5)
        {
            _initialDelay = initialDelay;
            _factor = factor;
            _maxVariableAttempts = maxVariableAttempts;
        }

        public TimeSpan CalculateDelayTime(int attempt)
        {
            if (attempt < 0)
            {
                throw new ArgumentOutOfRangeException(nameof(attempt), "Attempt must be a non-negative number.");
            }

            return attempt >= _maxVariableAttempts ?
                TimeSpan.FromMilliseconds(_initialDelay.TotalMilliseconds * Math.Pow(_factor, _maxVariableAttempts - 1)) :
                TimeSpan.FromMilliseconds(_initialDelay.TotalMilliseconds * Math.Pow(_factor, attempt));
        }
    }
}
