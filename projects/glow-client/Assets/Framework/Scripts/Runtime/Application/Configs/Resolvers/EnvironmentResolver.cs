using WPFramework.Domain.Models;
using WPFramework.Domain.Modules;

namespace WPFramework.Application.Configs
{
    public sealed class EnvironmentResolver : IEnvironmentResolver
    {
        EnvironmentModel _environmentModel;

        void IEnvironmentResolver.SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        IEnvironment IEnvironmentResolver.Resolve()
        {
            return new SpecifiedEnvironment(_environmentModel.Env);
        }
    }
}
