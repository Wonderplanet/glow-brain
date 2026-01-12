namespace WPFramework.Modules.Benchmark
{
    public record TimeMeasurementPointData(string PointName, double ElapsedTimeDelta, double TotalElapsedTime)
    {
        public string PointName { get; } = PointName;
        public double ElapsedTimeDelta { get; } = ElapsedTimeDelta;
        public double TotalElapsedTime { get; } = TotalElapsedTime;
    }
}
