using WPFramework.Domain.Models;

namespace WPFramework.Domain.Modules
{
    public interface IEnvironmentResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IEnvironment Resolve();
    }
}
