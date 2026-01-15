using System;
using Newtonsoft.Json;

namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    [Serializable]
    internal class GlowSigninRequestData
    {
        [JsonProperty(PropertyName = "id_token")]
        string _idToken;

        [JsonIgnore]
        public string IDToken => _idToken;

        public GlowSigninRequestData()
        {
        }

        public GlowSigninRequestData(string idToken)
        {
            _idToken = idToken;
        }
    }
}
