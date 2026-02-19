using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public sealed class ApiHostResolver : IApiHostResolver
    {
        EnvironmentModel _environmentModel;

        void IApiHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        public IApiHost Resolve()
        {
            return new SpecificApiHost(_environmentModel.Api);
        }
    }
}
