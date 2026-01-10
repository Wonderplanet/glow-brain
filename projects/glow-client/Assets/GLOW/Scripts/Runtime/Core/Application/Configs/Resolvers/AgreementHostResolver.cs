using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs.Resolvers
{
    public sealed class AgreementHostResolver : IAgreementHostResolver
    {
        EnvironmentModel _environmentModel;

        void IAgreementHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        public IAgreementHost Resolve()
        {
            return new SpecificAgreementHost(_environmentModel.AgreementCdn);
        }
    }
}
