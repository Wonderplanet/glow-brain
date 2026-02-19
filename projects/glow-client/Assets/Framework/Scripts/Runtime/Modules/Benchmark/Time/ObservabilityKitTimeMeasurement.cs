using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using WonderPlanet.ObservabilityKit;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.Benchmark
{
    public sealed class ObservabilityKitTimeMeasurement : ITimeMeasurement
    {
        const string DefaultTotalTimeTag = "totalTime";

        readonly List<TimeMeasurementPointData> _points;
        readonly Stopwatch _stopwatch = new Stopwatch();

        public string Name { get; }
        public string Category { get; }

        readonly string _sliTotalTimeTag;

        bool _isDisposed;

        public ObservabilityKitTimeMeasurement(string category, string name, int capacity = 100, string sliTotalTimeTag = DefaultTotalTimeTag)
        {
            // NOTE: キャパシティ設定をして事前に確保を行い途中でのリサイズを抑制する
            _points = new List<TimeMeasurementPointData>(capacity);
            Category = category;
            Name = name;

            _sliTotalTimeTag = sliTotalTimeTag;
        }

        void ITimeMeasurement.Start()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
            }

            _stopwatch.Start();
        }

        void ITimeMeasurement.Restart()
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
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
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
            }

            _stopwatch.Stop();
        }

        void ITimeMeasurement.AddPoint(string pointName)
        {
            if (_isDisposed)
            {
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
            }

            if (!_stopwatch.IsRunning)
            {
                ApplicationLog.LogWarning(nameof(ObservabilityKitTimeMeasurement), "Stopwatch is not running.");
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
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
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
                throw new ObjectDisposedException(nameof(ObservabilityKitTimeMeasurement));
            }

            // NOTE: ダンプで消化するログを一つにするためにStringBuilderを利用してログをまとめた後に出力する
            var builder = new StringBuilder();

            var totalTime = _stopwatch.Elapsed.TotalMilliseconds;
            builder.AppendLine($"{nameof(ObservabilityKitTimeMeasurement)} : [{Category} : {Name}] {totalTime} ms");

            builder.AppendLine($"{_points.Count} points");
            for (var num =0 ; num < _points.Count; ++num)
            {
                var point = _points[num];
                builder.AppendLine($"    {num:D03} - {point.PointName}: [Elapsed Time Delta = {point.ElapsedTimeDelta} ms][Total = {point.TotalElapsedTime} ms]");
            }

            // NOTE: ObservabilityKitへ通知する
            var dictionary = new Dictionary<string, object>
            {
                [_sliTotalTimeTag] = totalTime
            };
            foreach (var data in _points)
            {
                dictionary[data.PointName] = data.ElapsedTimeDelta;
            }
            // TODO: カスタムイベントを作成してログインに特化した情報にする可能性がある
            //       その際にはカテゴリ及び名前の２つの情報が必要となる
            ObservabilityKit.RecordLogger(Name, dictionary);

            ApplicationLog.Log(nameof(ObservabilityKitTimeMeasurement), builder.ToString());
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
