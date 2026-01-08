using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IAuthenticatorHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IAuthenticatorHost Resolve();
    }
}
