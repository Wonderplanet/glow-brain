using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificCdnHost : ICdnHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificCdnHost(string uri)
        {
            Uri = uri;
        }
    }
}
