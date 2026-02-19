using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Agreement
{
    [Serializable]
    public class AgreementConsentInfoResultsData
    {
        [DataMember(Name = "user_id")][JsonProperty("user_id")]
        public string UserId { get; set; }
        [DataMember(Name = "details")][JsonProperty("details")]
        public AgreementConsentInfoDetailsData[] Details { get; set; }
    }
}
