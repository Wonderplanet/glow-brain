using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public class BannerCdnHostResolver : IBannerCdnHostResolver
    {
        EnvironmentModel _environmentModel;

        void IBannerCdnHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        IBannerCdnHost IBannerCdnHostResolver.Resolve()
        {
            return new SpecificBannerCdnHost(_environmentModel.BannerCdn);
        }
    }
}