using GLOW.Core.Domain.Hosts;
using WPFramework.Domain.Models;

namespace GLOW.Core.Domain.Resolvers
{
    public interface IAnnouncementCdnHostResolver
    {
        void SetEnvironment(EnvironmentModel environment);
        IAnnouncementCdnHost Resolve();
    }
}