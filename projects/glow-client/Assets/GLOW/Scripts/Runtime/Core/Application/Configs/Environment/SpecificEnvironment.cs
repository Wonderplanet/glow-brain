using WPFramework.Domain.Modules;
using Zenject.Internal;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificEnvironment : IEnvironment
    {
        public string EnvironmentName { get; }

        [Preserve]
        public SpecificEnvironment(string environmentName)
        {
            EnvironmentName = environmentName;
        }
    }
}
