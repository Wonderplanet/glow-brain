using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IApiHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IApiHost Resolve();
    }
}