namespace GLOW.Core.Domain.TimeMeasurement
{
    public interface IInGameLoadingMeasurement
    {
        void Start();
        void Stop();
        void ReportAndClear();
        void Clear();
    }
}
