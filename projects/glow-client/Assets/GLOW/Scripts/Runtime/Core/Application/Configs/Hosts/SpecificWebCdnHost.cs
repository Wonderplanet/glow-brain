using GLOW.Core.Domain.Hosts;
using ModestTree.Util;

namespace GLOW.Core.Application.Configs
{
    public class SpecificWebCdnHost : IWebCdnHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificWebCdnHost(string uri)
        {
            Uri = uri;
        }
    }
}
