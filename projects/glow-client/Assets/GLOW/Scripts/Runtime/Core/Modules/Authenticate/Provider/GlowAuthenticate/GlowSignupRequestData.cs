using System;
using Newtonsoft.Json;

namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    [Serializable]
    public class GlowSignupRequestData
    {
        [JsonProperty(PropertyName = "clientUuid")]
        string _clientUuid;

        [JsonIgnore]
        public string ClientUuid => _clientUuid;

        public GlowSignupRequestData()
        {
        }
        
        public GlowSignupRequestData(string clientUuid)
        {
            _clientUuid = clientUuid;
        }
    }
}