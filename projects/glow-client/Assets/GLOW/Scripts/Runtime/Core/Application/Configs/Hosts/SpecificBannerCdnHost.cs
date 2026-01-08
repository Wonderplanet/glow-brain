using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public class SpecificBannerCdnHost : IBannerCdnHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificBannerCdnHost(string uri)
        {
            Uri = uri;
        }
    }
}