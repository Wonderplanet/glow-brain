
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IApiContextHostBuilder
    {
        void Build(EnvironmentModel environment);
    }
}