using WPFramework.Modules.Environment;

namespace WPFramework.Debugs.Environment.Data.Data
{
    public record DebugEnvironmentSpecifiedDomainData(EnvironmentData SpecifiedEnvironmentData)
    {
        public EnvironmentData SpecifiedEnvironmentData { get; } = SpecifiedEnvironmentData;
    }
}
