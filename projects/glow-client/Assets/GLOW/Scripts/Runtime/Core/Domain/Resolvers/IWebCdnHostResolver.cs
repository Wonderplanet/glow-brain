using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IWebCdnHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IWebCdnHost Resolve();
    }
}
