using System;
using Newtonsoft.Json;

namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    [Serializable]
    internal class GlowSignupResponse
    {
        [JsonProperty(PropertyName = "id_token")]
        string _idToken;

        [JsonIgnore]
        public string IDToken => _idToken;

        public GlowSignupResponse()
        {
        }

        public GlowSignupResponse(string idToken)
        {
            _idToken = idToken;
        }
    }
}
