using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public sealed class AssetCdnHostResolver : IAssetCdnHostResolver
    {
        EnvironmentModel _environmentModel;

        void IAssetCdnHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        ICdnHost IAssetCdnHostResolver.Resolve()
        {
            return new SpecificCdnHost(_environmentModel.AssetCdn);
        }
    }
}
