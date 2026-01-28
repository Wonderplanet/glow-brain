using System;

namespace WPFramework.Modules.Benchmark
{
    public interface ITimeMeasurement : IDisposable
    {
        string Name { get; }
        string Category { get; }

        void Start();
        void Restart();
        void Clear();
        void Stop();
        void Report();
        void AddPoint(string pointName);
    }
}
