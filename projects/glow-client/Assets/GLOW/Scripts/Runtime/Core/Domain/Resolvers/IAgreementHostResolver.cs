using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IAgreementHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IAgreementHost Resolve();
    }
}
