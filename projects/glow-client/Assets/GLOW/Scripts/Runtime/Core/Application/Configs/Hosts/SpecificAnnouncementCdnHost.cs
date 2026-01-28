using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificAnnouncementCdnHost : IAnnouncementCdnHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificAnnouncementCdnHost(string uri)
        {
            Uri = uri;
        }
    }
}