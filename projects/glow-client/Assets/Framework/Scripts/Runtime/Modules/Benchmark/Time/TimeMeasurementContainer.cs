using System.Collections.Generic;

namespace WPFramework.Modules.Benchmark
{
    public sealed class TimeMeasurementContainer
    {
        readonly Dictionary<string, ITimeMeasurement> _timeMeasurementTable = new Dictionary<string, ITimeMeasurement>();

        public void AddTimeMeasurement(ITimeMeasurement timeMeasurement)
        {
            _timeMeasurementTable.Add(timeMeasurement.Name, timeMeasurement);
        }

        public bool RemoveTimeMeasurement(string name)
        {
            return _timeMeasurementTable.Remove(name);
        }

        public ITimeMeasurement GetTimeMeasurement(string name)
        {
            return _timeMeasurementTable.GetValueOrDefault(name);
        }
    }
}
