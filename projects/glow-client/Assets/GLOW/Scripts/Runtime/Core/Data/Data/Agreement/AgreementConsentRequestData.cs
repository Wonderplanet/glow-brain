using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Agreement
{
    [Serializable]
    public class AgreementConsentRequestData
    {
        [DataMember(Name = "ok")][JsonProperty("ok")]
        public bool Ok { get; set; }
        [DataMember(Name = "results")][JsonProperty("results")]
        public AgreementConsentRequestResultsData Results { get; set; }
    }
}
