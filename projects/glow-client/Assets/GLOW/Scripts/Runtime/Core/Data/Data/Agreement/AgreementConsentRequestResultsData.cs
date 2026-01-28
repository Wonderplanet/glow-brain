using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Agreement
{
    [Serializable]
    public class AgreementConsentRequestResultsData
    {
        [DataMember(Name = "url")][JsonProperty("url")]
        public string Url { get; set; }
    }
}
