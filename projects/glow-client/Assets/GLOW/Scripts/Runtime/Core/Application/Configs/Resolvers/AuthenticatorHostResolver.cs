using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public sealed class AuthenticatorHostResolver : IAuthenticatorHostResolver
    {
        EnvironmentModel _environmentModel;

        void IAuthenticatorHostResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        public IAuthenticatorHost Resolve()
        {
            return new SpecificAuthenticatorHost(_environmentModel.Api);
        }
    }
}
