using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Domain.Models
{
    public record DebugEnvironmentSpecifiedDomainModel(EnvironmentModel SpecifiedEnvironment)
    {
        public EnvironmentModel SpecifiedEnvironment { get; } = SpecifiedEnvironment;
    }
}
