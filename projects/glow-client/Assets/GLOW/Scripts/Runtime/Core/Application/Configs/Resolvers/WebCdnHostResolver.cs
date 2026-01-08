using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public sealed class WebCdnHostResolver : IWebCdnHostResolver
    {
        EnvironmentModel _environmentModel;

        void IWebCdnHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        IWebCdnHost IWebCdnHostResolver.Resolve()
        {
            return new SpecificWebCdnHost(_environmentModel.WebCdn);
        }
    }
}
