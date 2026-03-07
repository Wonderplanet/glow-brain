using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public sealed class MstCdnHostResolver : IMstCdnHostResolver
    {
        EnvironmentModel _environmentModel;

        void IMstCdnHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        IMstHost IMstCdnHostResolver.Resolve()
        {
            return new SpecificMstHost(_environmentModel.MasterCdn);
        }
    }
}
