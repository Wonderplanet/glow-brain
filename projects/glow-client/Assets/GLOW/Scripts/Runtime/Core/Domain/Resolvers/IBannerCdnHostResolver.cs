using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IBannerCdnHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IBannerCdnHost Resolve();
    }
}