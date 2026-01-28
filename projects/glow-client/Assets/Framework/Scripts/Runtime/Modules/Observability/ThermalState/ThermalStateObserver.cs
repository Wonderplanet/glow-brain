using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.ThermalState;
using WonderPlanet.ObservabilityKit;

namespace WPFramework.Modules.Observability
{
    public interface IThermalStateObserver
    {
        void StartObserve();
        void StopObserve();
    }
    
    public class ThermalStateObserver : IDisposable, IThermalStateObserver
    {
        const string MetricName = "thermal_state";
        const int IntervalSeconds = 60;
        CancellationTokenSource _cts;

        public void StartObserve()
        {
            if (_cts != null)
            {
                Debug.LogWarning("ThermalObserver: Already observing");
                return;
            }
            _cts = new CancellationTokenSource();
            Process(_cts.Token).Forget();
        }
        
        public void StopObserve()
        {
            _cts?.Cancel();
            _cts?.Dispose();
            _cts = null;
        }

        async UniTask Process(CancellationToken token)
        {
            while (!token.IsCancellationRequested)
            {
                var state = ThermalMonitor.GetState();
                if (state != ThermalState.Unknown)
                {
                    ObservabilityKit.RecordMetric(MetricName, (int)state);
                }
                await UniTask.Delay(TimeSpan.FromSeconds(IntervalSeconds), cancellationToken: token);
            }
        }

        public void Dispose()
        {
            StopObserve();
        }
    }
}