using System;
using Newtonsoft.Json;

namespace GLOW.Core.Modules.Authenticate.Token
{
    [Serializable]
    public class GlowIDTokenData
    {
        [JsonProperty("id_token")]
        string _idToken;

        [JsonProperty("identifier")]
        string _identifier;

        [JsonIgnore]
        public string IDToken => _idToken;

        [JsonIgnore]
        public string Identifier => _identifier;

        public GlowIDTokenData()
        {
        }

        public GlowIDTokenData(string idToken, string identifier)
        {
            _idToken = idToken;
            _identifier = identifier;
        }
    }
}
