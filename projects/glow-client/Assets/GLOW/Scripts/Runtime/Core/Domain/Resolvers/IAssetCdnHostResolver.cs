using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IAssetCdnHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        ICdnHost Resolve();
    }
}
