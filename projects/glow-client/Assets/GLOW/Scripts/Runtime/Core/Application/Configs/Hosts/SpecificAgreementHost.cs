using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificAgreementHost : IAgreementHost
    {
        public string Uri { get; }

        [Preserve]
        public SpecificAgreementHost(string uri)
        {
            Uri = uri;
        }
    }
}
