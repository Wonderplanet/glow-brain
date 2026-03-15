using System;
using Newtonsoft.Json;

namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    [Serializable]
    internal class GlowSigninResponse
    {
        [JsonProperty(PropertyName = "access_token")]
        string _accessToken;

        [JsonIgnore]
        public string AccessToken => _accessToken;

        public GlowSigninResponse()
        {
        }

        public GlowSigninResponse(string accessToken)
        {
            _accessToken = accessToken;
        }
    }
}
