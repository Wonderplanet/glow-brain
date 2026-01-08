using GLOW.Core.Domain.Hosts;
using GLOW.Core.Domain.Resolvers;
using WPFramework.Domain.Models;

namespace GLOW.Core.Application.Configs
{
    public class AnnouncementCdnHostResolver : IAnnouncementCdnHostResolver
    {
        EnvironmentModel _environmentModel;
        
        public void SetEnvironment(EnvironmentModel environment)
        {
            _environmentModel = environment;
        }

        public IAnnouncementCdnHost Resolve()
        {
            return new SpecificAnnouncementCdnHost(_environmentModel.AnnouncementCdn);
        }
    }
}