using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public class SpecificMstHost : IMstHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificMstHost(string uri)
        {
            Uri = uri;
        }
    }
}
