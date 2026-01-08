using GLOW.Core.Constants;
using GLOW.Core.Domain.Hosts;
using UnityEngine.Scripting;

namespace GLOW.Core.Application.Configs
{
    public sealed class SpecificAuthenticatorHost : IAuthenticatorHost
    {
        public string Uri { get; }
        public string Password => Credentials.AuthenticatorPw;

        [Preserve]
        public SpecificAuthenticatorHost(string uri)
        {
            Uri = uri;
        }
    }
}
