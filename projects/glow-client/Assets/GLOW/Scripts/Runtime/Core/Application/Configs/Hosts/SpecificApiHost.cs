using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificApiHost : IApiHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificApiHost(string uri)
        {
            Uri = uri;
        }
    }
}
