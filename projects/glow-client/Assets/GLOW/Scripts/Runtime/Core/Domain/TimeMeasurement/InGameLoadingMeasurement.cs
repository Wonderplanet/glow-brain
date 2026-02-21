using System;
using GLOW.Core.Constants.Benchmark;
using WonderPlanet.ObservabilityKit;

namespace GLOW.Core.Domain.TimeMeasurement
{
    public class InGameLoadingMeasurement : IInGameLoadingMeasurement, IDisposable
    {
        // SystemのStopwatchクラスを利用している関係で、
        // 1計測1インスタンス想定でログを作成する
        ITimeMeasurement _timeMeasurement;

        void IInGameLoadingMeasurement.Start()
        {
            _timeMeasurement ??= new WonderPlanet.ObservabilityKit.TimeMeasurement(TimeBenchmark.Name.InGame);
            _timeMeasurement.Start();//ここだけ明示的に?演算子使ってない(nullであっては困る)
        }

        void IInGameLoadingMeasurement.Stop()
        {
            _timeMeasurement?.Stop();
        }

        void IInGameLoadingMeasurement.ReportAndClear()
        {
            _timeMeasurement?.Report();
            Clear();
        }

        void IInGameLoadingMeasurement.Clear()
        {
            Clear();
        }

        void Clear()
        {
            _timeMeasurement?.Clear();
            _timeMeasurement = null;
        }

        public void Dispose()
        {
            // _timeMeasurement.Disposeは、実装中でreport呼ばるからあんまり呼びたくない
            Clear();
        }
    }
}
