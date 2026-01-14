using WPFramework.Domain.Modules;

namespace GLOW.Core.Application.Configs
{
    public sealed class EnvironmentHostResolver : IEnvironmentHostResolver
    {
        IEnvironmentHost IEnvironmentHostResolver.Resolve()
        {
            return new EnvironmentHost();
        }
    }
}
