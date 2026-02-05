using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Benchmark
{
    public sealed class TimeMeasurement : ITimeMeasurement
    {
        readonly List<TimeMeasurementPointData> _points;
        readonly Stopwatch _stopwatch = new Stopwatch();

        public string Name { get; }
        public string Category { get; }

        bool _isDisposed;

        public TimeMeasurement(string category, string name, int capacity = 100)
        {
            // NOTE: キャパシティ設定をして事前に確保を行い途中でのリサイズを抑制する
            _points = new List<TimeMeasurementPointData>(capacity);
            Category = category;
            Name = name;
        }

        void ITimeMeasurement.Start()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            _stopwatch.Start();
        }

        void ITimeMeasurement.Restart()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            _stopwatch.Restart();
        }

        void ITimeMeasurement.Stop()
        {
            Stop();
        }

        void Stop()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            _stopwatch.Stop();
        }

        void ITimeMeasurement.AddPoint(string pointName)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            if (!_stopwatch.IsRunning)
            {
                ApplicationLog.LogWarning(nameof(TimeMeasurement), "Stopwatch is not running.");
                return;
            }

            if (_points.Count == 0)
            {
                // NOTE: 最初の計測の場合は経過時間がないため経過時間と合計時間は同じ値となる
                _points.Add(
                    new TimeMeasurementPointData(
                        pointName,
                        _stopwatch.Elapsed.TotalMilliseconds,
                        _stopwatch.Elapsed.TotalMilliseconds));
            }
            else
            {
                var lastPoint = _points[_points.Count - 1];
                _points.Add(
                    new TimeMeasurementPointData(
                        pointName,
                        _stopwatch.Elapsed.TotalMilliseconds - lastPoint.ElapsedTimeDelta,
                        _stopwatch.Elapsed.TotalMilliseconds));
            }
        }

        void ITimeMeasurement.Clear()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            _points.Clear();
        }

        void ITimeMeasurement.Report()
        {
            Report();
        }

        void Report()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(TimeMeasurement));
            }

            // NOTE: ダンプで消化するログを一つにするためにStringBuilderを利用してログをまとめた後に出力する
            var builder = new StringBuilder();

            builder.AppendLine($"{nameof(TimeMeasurement)} : [{Category} : {Name}] {_stopwatch.Elapsed.TotalMilliseconds} ms");

            builder.AppendLine($"{_points.Count} points");
            for (var num =0 ; num < _points.Count; ++num)
            {
                var point = _points[num];
                builder.AppendLine($"    {num:D03} - {point.PointName}: [Elapsed Time Delta = {point.ElapsedTimeDelta} ms][Total = {point.TotalElapsedTime} ms]");
            }

            ApplicationLog.Log(nameof(TimeMeasurement), builder.ToString());
        }

        public void Dispose()
        {
            if (_isDisposed)
            {
                return;
            }

            Stop();
            Report();

            // NOTE: StopとReportがDisposeフラグを見ているため最後にフラグを変える
            _isDisposed = true;
        }
    }
}
