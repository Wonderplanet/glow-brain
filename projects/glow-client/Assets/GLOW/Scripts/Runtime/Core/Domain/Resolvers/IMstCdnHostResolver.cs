using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IMstCdnHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IMstHost Resolve();
    }
}
