using WPFramework.Domain.Modules;

namespace WPFramework.Application.Configs
{
    public sealed class SpecifiedEnvironment : IEnvironment
    {
        public string EnvironmentName { get; }

        public SpecifiedEnvironment(string environmentName)
        {
            EnvironmentName = environmentName;
        }
    }
}
